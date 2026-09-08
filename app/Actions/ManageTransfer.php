<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TransactionStatus;
use App\Models\Book;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ManageTransfer
{
    /** @param array<string, mixed> $input */
    public function save(User $user, Book $book, array $input, ?string $group = null): string
    {
        Gate::forUser($user)->authorize('update', $book);

        return DB::transaction(function () use ($book, $input, $group): string {
            $data = Validator::make($input, [
                'source_account_id' => ['required', 'integer'],
                'destination_account_id' => ['required', 'integer', 'different:source_account_id'],
                'amount_minor' => ['required', 'integer', 'min:1', 'max:'.Money::MaxMinor],
                'occurred_on' => ['required', 'date_format:Y-m-d'],
                'status' => ['required', Rule::enum(TransactionStatus::class)],
                'memo' => ['nullable', 'string', 'max:65535'],
            ])->validate();

            $accounts = $book->accounts()->whereIn('id', [$data['source_account_id'], $data['destination_account_id']])
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach (['source_account_id', 'destination_account_id'] as $field) {
                $account = $accounts->get($data[$field]);

                if ($account === null || $account->archived_at !== null) {
                    throw ValidationException::withMessages([$field => 'Choose an active account in this book.']);
                }

                if ($account->opened_on->toDateString() > $data['occurred_on']) {
                    throw ValidationException::withMessages(['occurred_on' => 'The transfer cannot predate either account.']);
                }
            }

            $entries = $group === null ? collect() : $book->transactions()
                ->where('transfer_group_id', $group)->orderBy('id')->lockForUpdate()->get();

            if ($group !== null) {
                abort_if($entries->isEmpty(), 404);

                if ($entries->count() !== 2 || $entries->sum('amount_minor') !== 0 ||
                    $entries->where('amount_minor', '<', 0)->count() !== 1) {
                    throw ValidationException::withMessages(['transfer' => 'The transfer pair is inconsistent.']);
                }
            }

            $group ??= (string) Str::uuid();

            foreach (['source_account_id' => -1, 'destination_account_id' => 1] as $field => $sign) {
                $entry = $entries->first(fn (Transaction $entry): bool => ($entry->amount_minor < 0) === ($sign < 0));
                $attributes = ['account_id' => $data[$field], 'category_id' => null,
                    'amount_minor' => $sign * (int) $data['amount_minor'], 'occurred_on' => $data['occurred_on'],
                    'status' => $data['status'], 'memo' => $data['memo'] ?? null, 'transfer_group_id' => $group];

                if ($entry !== null) {
                    $entry->update($attributes);

                    continue;
                }

                $book->transactions()->create($attributes);
            }

            return $group;
        });
    }

    public function delete(User $user, Book $book, string $group): void
    {
        Gate::forUser($user)->authorize('update', $book);

        DB::transaction(function () use ($book, $group): void {
            $entries = $book->transactions()->where('transfer_group_id', $group)->lockForUpdate()->get();
            abort_if($entries->isEmpty(), 404);

            foreach ($entries as $entry) {
                $entry->delete();
            }
        });
    }
}

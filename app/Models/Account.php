<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountType;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['book_id', 'name', 'type', 'opening_balance_minor', 'opened_on'])]
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    /** @return BelongsTo<Book, $this> */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /** @return HasMany<Transaction, $this> */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /** @return array{type: class-string<AccountType>, opening_balance_minor: 'integer', opened_on: 'date', archived_at: 'datetime'} */
    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'opening_balance_minor' => 'integer',
            'opened_on' => 'date',
            'archived_at' => 'datetime',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionStatus;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'book_id',
    'account_id',
    'category_id',
    'occurred_on',
    'payee',
    'reference',
    'memo',
    'amount_minor',
    'status',
    'transfer_group_id',
])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    /** @return BelongsTo<Book, $this> */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<Transaction, $this> */
    public function transferTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'transfer_group_id', 'transfer_group_id');
    }

    /** @return array{occurred_on: 'date', amount_minor: 'integer', status: class-string<TransactionStatus>} */
    protected function casts(): array
    {
        return [
            'occurred_on' => 'date',
            'amount_minor' => 'integer',
            'status' => TransactionStatus::class,
        ];
    }
}

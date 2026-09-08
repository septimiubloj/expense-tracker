<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BudgetPeriodStatus;
use Database\Factories\BudgetPeriodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['book_id', 'name', 'starts_on', 'ends_on', 'status'])]
class BudgetPeriod extends Model
{
    /** @use HasFactory<BudgetPeriodFactory> */
    use HasFactory;

    /** @return BelongsTo<Book, $this> */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /** @return HasMany<BudgetAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(BudgetAllocation::class);
    }

    /** @return array{starts_on: 'date', ends_on: 'date', status: class-string<BudgetPeriodStatus>} */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'status' => BudgetPeriodStatus::class,
        ];
    }
}

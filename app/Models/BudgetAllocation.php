<?php

namespace App\Models;

use Database\Factories\BudgetAllocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['budget_period_id', 'category_id', 'planned_amount_minor'])]
class BudgetAllocation extends Model
{
    /** @use HasFactory<BudgetAllocationFactory> */
    use HasFactory;

    /** @return BelongsTo<BudgetPeriod, $this> */
    public function budgetPeriod(): BelongsTo
    {
        return $this->belongsTo(BudgetPeriod::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function casts(): array
    {
        return [
            'planned_amount_minor' => 'integer',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'project_id',
    'item_name',
    'unit',
    'unit_price',
    'total_price',
])]
class BudgetItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (BudgetItem $budgetItem): void {
            $budgetItem->total_price = (float) ($budgetItem->unit_price ?? 0);
        });

        static::saved(function (BudgetItem $budgetItem): void {
            // Sync to expenses table so project budgets show up under expenses/budgeting
            Expense::updateOrCreate(
                [
                    'project_id' => $budgetItem->project_id,
                    'description' => 'Budget: ' . $budgetItem->item_name,
                ],
                [
                    'user_id' => auth()->id() ?? $budgetItem->project?->user_id ?? 1,
                    'expense_date' => now(),
                    'category' => 'other',
                    'amount' => $budgetItem->total_price,
                ]
            );
        });

        static::deleted(function (BudgetItem $budgetItem): void {
            Expense::where('project_id', $budgetItem->project_id)
                ->where('description', 'Budget: ' . $budgetItem->item_name)
                ->delete();
        });
    }

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

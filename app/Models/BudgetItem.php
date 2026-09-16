<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (BudgetItem $budgetItem): void {
            $budgetItem->total_price = (float) ($budgetItem->quantity ?? 0)
                * (float) ($budgetItem->unit_price ?? 0);
        });
    }

    protected $fillable = [
        'project_id',
        'item_name',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

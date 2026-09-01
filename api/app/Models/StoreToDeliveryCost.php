<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreToDeliveryCost extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'store_to_delivery_costs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'cost_domicile' => 'decimal:2',
            'cost_stopdesk' => 'decimal:2',
            'cost_cancel' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function deliveryCompany(): BelongsTo
    {
        return $this->belongsTo(DeliveryCompany::class, 'delivery_company_id');
    }

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class);
    }
}

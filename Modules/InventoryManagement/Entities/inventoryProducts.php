<?php

namespace Modules\InventoryManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class inventoryProducts extends Model
{
    protected $table = 'inventory_products';

    protected $guarded = ['id'];

    public function InventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'inventory_product_id', 'id');
    }
}

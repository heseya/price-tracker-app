<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperProductPrice
 */
class ProductPrice extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'price_min',
        'price_max',
        'changed_at',
        'currency',
    ];

    protected $casts = [
        'price_min' => 'float',
        'price_max' => 'float',
    ];

    protected $dates = [
        'changed_at',
    ];
}

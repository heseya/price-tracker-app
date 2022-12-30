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
        'price',
        'changed_at',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    protected $dates = [
        'changed_at',
    ];
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperApi
 */
class Api extends Model
{
    protected $fillable = [
        'url',
        'version',
        'integration_token',
        'refresh_token',
        'uninstall_token',
        'auth_token',
        'furgonetka_token',
    ];
}

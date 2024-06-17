<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Customers.
 *
 * @package namespace App\Entities;
 */
class Customers extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'internal_id',
        'descriptive_name',
        'client_customer',
        'level',
        'manager',
        'currency_code',
        'time_zone',
        'hidden',
        'resource_name',
        'test_account',
        'applied_labels',
        'status',
    ];

}

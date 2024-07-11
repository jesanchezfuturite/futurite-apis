<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Adscustomersclients.
 *
 * @package namespace App\Entities;
 */
class Adscustomersclients extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'ads_customers_clients';

    protected $fillable = ['customer_id','client_id'];

}

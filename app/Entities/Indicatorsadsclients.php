<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Indicatorsadsclients.
 *
 * @package namespace App\Entities;
 */
class Indicatorsadsclients extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'ads_indicators_clients';

    protected $fillable = [
        'customer_id',
        'campaign_id',
        'client_id',
        'impressions',
        'impressions_month',
        'impressions_last_month',
        'clics',
        'clics_month',
        'clics_last_month',
        'conversion',
        'conversion_month',
        'conversion_last_month',
        'paid',
        'paid_month',
        'paid_last_month',
        'budget',

    ];

}

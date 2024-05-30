<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Campaigns.
 *
 * @package namespace App\Entities;
 */
class Campaigns extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'campaign_id',
        'name',
        'status',
        'clicks',
        'impressions',
        'ctr',
        'average_cpc',
        'cost_micros',
    ];

}

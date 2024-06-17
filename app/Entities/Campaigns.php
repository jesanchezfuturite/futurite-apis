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
        'customer_id',
        'campaign_id',
        'name',
        'status',
        'serving_status',
        'advertising_channel_type',
        'advertising_channel_sub_type',
        'start_date',
        'end_date',
        'bidding_strategy_type',
        'campaign_budget',
        'labels',
        'tracking_url_template',
        'final_url_suffix',
        'frequency_caps',
        'video_brand_safety_suitability',
        'experiment_type',
        'optimization_score',
    ];

}

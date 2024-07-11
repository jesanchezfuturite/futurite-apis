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
        'customer_id',
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


    /**
     * ads_customer_clients_relationship
     */
    public function adsAssociated(){
        return $this->hasMany(\App\Entities\Adscustomersclients::class, 'customer_id','customer_id');
    }

    /**
     * Get customers not in ads_customers_clients and with status 2
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function customersNotInAds()
    {
        return self::whereNotIn('customer_id', function ($query) {
            $query->select('customer_id')
                  ->from('ads_customers_clients');
        })->where('status', 2)->orderBy('descriptive_name', 'asc')->get();
    }

    /**
     * Get customers associated with a specific client_id
     *
     * @param int $clientId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function customersByClientId($clientId)
    {
        return self::whereHas('adsAssociated', function ($query) use ($clientId) {
            $query->where('client_id', $clientId);
        })->get();
    }


    /**
     * Get the total number of campaigns for a specific client_id
     *
     * @param int $clientId
     * @return int
     */
    public static function campaignsCountByClientId($clientId)
    {
        return self::whereHas('adsAssociated', function ($query) use ($clientId) {
            $query->where('client_id', $clientId);
        })->with(['adsAssociated' => function ($query) {
            $query->select('customer_id');
        }])->get()
          ->pluck('adsAssociated')
          ->flatten()
          ->map(function ($adsCustomerClient) {
              return Campaigns::where('customer_id', $adsCustomerClient->customer_id)->count();
          })->sum();
    }

}

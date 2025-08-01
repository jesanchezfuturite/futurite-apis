<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Ongoingclientes.
 *
 * @package namespace App\Entities;
 */
class Ongoingclientes extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $connection = 'ongoing';
    protected $table = 'clientes';
    protected $fillable = [];


    public function services(){
        return $this->hasMany(\App\Entities\Ongoingclienteservicios::class, 'cliente_id','id');
    }

}

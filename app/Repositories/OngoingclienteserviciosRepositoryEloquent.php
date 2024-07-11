<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\OngoingclienteserviciosRepository;
use App\Entities\Ongoingclienteservicios;
use App\Validators\OngoingclienteserviciosValidator;

/**
 * Class OngoingclienteserviciosRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class OngoingclienteserviciosRepositoryEloquent extends BaseRepository implements OngoingclienteserviciosRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Ongoingclienteservicios::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}

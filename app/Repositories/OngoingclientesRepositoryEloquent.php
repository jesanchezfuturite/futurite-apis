<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\OngoingclientesRepository;
use App\Entities\Ongoingclientes;
use App\Validators\OngoingclientesValidator;

/**
 * Class OngoingclientesRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class OngoingclientesRepositoryEloquent extends BaseRepository implements OngoingclientesRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Ongoingclientes::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}

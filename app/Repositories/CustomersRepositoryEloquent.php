<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\CustomersRepository;
use App\Entities\Customers;
use App\Validators\CustomersValidator;

/**
 * Class CustomersRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class CustomersRepositoryEloquent extends BaseRepository implements CustomersRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Customers::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}

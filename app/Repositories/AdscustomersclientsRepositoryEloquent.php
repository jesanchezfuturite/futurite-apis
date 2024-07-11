<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\AdscustomersclientsRepository;
use App\Entities\Adscustomersclients;
use App\Validators\AdscustomersclientsValidator;

/**
 * Class AdscustomersclientsRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class AdscustomersclientsRepositoryEloquent extends BaseRepository implements AdscustomersclientsRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Adscustomersclients::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}

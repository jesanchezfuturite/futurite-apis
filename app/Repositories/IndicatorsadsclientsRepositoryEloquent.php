<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\IndicatorsadsclientsRepository;
use App\Entities\Indicatorsadsclients;
use App\Validators\IndicatorsadsclientsValidator;

/**
 * Class IndicatorsadsclientsRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class IndicatorsadsclientsRepositoryEloquent extends BaseRepository implements IndicatorsadsclientsRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Indicatorsadsclients::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}

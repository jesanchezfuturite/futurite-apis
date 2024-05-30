<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\CampaignsRepository;
use App\Entities\Campaigns;
use App\Validators\CampaignsValidator;

/**
 * Class CampaignsRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class CampaignsRepositoryEloquent extends BaseRepository implements CampaignsRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Campaigns::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}

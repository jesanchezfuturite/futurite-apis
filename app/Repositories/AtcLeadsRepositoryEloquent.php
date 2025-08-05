<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\AtcLeadsRepository;
use App\Entities\AtcLeads;
use App\Validators\AtcLeadsValidator;

/**
 * Class AtcLeadsRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class AtcLeadsRepositoryEloquent extends BaseRepository implements AtcLeadsRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return AtcLeads::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}

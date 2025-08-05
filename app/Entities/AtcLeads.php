<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

/**
 * Class AtcLeads.
 *
 * @package namespace App\Entities;
 */
class AtcLeads extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "contact_id",
        "name",
        "email",
        "phone",
        "campaign",
        "utmSource",
        "utmMedium",
        "utmContent",
        "utmTerm",
        "utmKeyword",
        "utmMatchtype",
        "date_created",
        "fullData"
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fullData' => AsArrayObject::class,
        ];
    }
}

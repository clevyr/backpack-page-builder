<?php

namespace Clevyr\PageBuilder\app\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Class PageSectionsPivot
 * @package Clevyr\PageBuilder\app\Models
 */
class PageSectionsPivot extends Model
{
    use SoftDeletes;
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    /**
     * @var string $table
     */
    protected $table = 'pages_sections_pivot';

    /**
     * @var string $primaryKey
     */
    protected $primaryKey = 'id';

    /**
     * @var bool $timestamps
     */
    public $timestamps = true;

    /**
     * @var string[] $fillable
     */
    protected $guarded = ['id'];

    /**
     * @var string[] $casts
     */
    protected $casts = [
        'data' => 'array',
    ];

    public static function booted()
    {
        // On creating assign a uuid
        static::creating(function ($model) {
            $model->attributes['uuid'] = Str::uuid();
        });

        parent::booted();
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Section
     *
     * @return HasOne
     */
    public function section() : HasOne
    {
        return $this->hasOne(PageSection::class, 'id', 'section_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get Formatted Data Attribute
     *
     * Json decodes or returns the original section data
     *
     * @return mixed
     */
    public function getFormattedDataAttribute()
    {
        if (!is_null($this->data)) {
            $data = $this->data;

            foreach ($data as $key => $d) {
                $data[$key] = json_decode($d, true) ?? $d;
            }

            return $data;
        }

        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}

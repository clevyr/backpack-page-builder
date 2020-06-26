<?php

namespace Clevyr\PageBuilder\app\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PageBuilder
 * @package Clevyr\PageBuilder\app\Models
 */
class Page extends Model
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
    protected $table = 'pages';

    /**
     * @var string $primaryKey
     */
    protected $primaryKey = 'id';

    /**
     * @var bool $timestamps
     */
    public $timestamps = true;

    /**
     * @var string[] $guarded
     */
    public $guarded = ['id'];

    /**
     * @var string[] $fakeColumns
     */
    protected $fakeColumns = ['extras'];

    /**
     * @var string[] $casts
     */
    protected $casts = [
        'extras' => 'array',
    ];

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
     * View
     *
     * Returns the view
     *
     * @return HasOne
     */
    public function view() : HasOne
    {
        return $this->hasOne(PageView::class, 'id', 'page_view_id');
    }

    /**
     * Sections
     *
     * Returns a list of the sections in the view
     *
     * @return BelongsToMany
     */
    public function sections() : BelongsToMany
    {
        return $this->belongsToMany(
            PageSection::class,
            PageSectionsPivot::class,
            'page_id',
            'section_id',
        )
            ->wherePivot('deleted_at', '=', null)
            ->withPivot(['uuid', 'id', 'data', 'deleted_at']);
    }

    /**
     * Trashed Sections
     *
     * Returns a list of trashed sections
     *
     * @return BelongsToMany
     */
    public function trashedSections() : BelongsToMany
    {
        return $this->belongsToMany(
            PageSection::class,
            PageSectionsPivot::class,
            'page_id',
            'section_id',
        )
            ->wherePivot('deleted_at', '!=', null)
            ->withPivot(['uuid', 'id', 'data', 'deleted_at']);
    }

    /**
     * Section Data
     *
     * Returns the section data
     *
     * @return HasMany
     */
//    public function sectionData() : HasMany
//    {
//        return $this->hasMany(
//            PageSectionsPivot::class,
//            'page_id',
//            'id',
//        );
//    }

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

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}

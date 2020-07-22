<?php

namespace Clevyr\PageBuilder\app\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Venturecraft\Revisionable\Revisionable;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * Class PageBuilder
 * @package Clevyr\PageBuilder\app\Models
 */
class Page extends Model
{
    use SoftDeletes;
    use CrudTrait;
    use RevisionableTrait;

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

    /**
     * @var string[] $appends
     */
    protected $appends = [
        'has_sub_pages',
        'url',
    ];

    /**
     * @var string[]
     */
    protected $dontKeepRevisionOf = [
        'deleted_at',
        'order',
        'page_view_id',
        'folder_name',
        'deleted_at',
        'created_at',
        'updated_at',
        'parent_id',
        'lft',
        'rgt',
        'depth'
    ];

    /**
     * @return string
     */
    public function identifiableName() : string
    {
        return $this->title;
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function menu()
    {
        return $this->where('parent_id', null)
            ->with(['subpages' => function ($query) {
                return $query->orderBy('lft');
            }])
            ->orderBy('lft');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * @return mixed
     */
    public function revisionHistory()
    {
        return $this->morphMany(get_class(Revisionable::newModel()), 'revisionable')
            ->orderBy('created_at', 'DESC');
    }

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
     * View
     *
     * Returns the view
     *
     * @return HasOne
     */
    public function activeViews() : HasOne
    {
        return $this->hasOne(PageView::class, 'id', 'page_view_id')
            ->where('deleted_at', '=', null);
    }

    /**
     * View
     *
     * Returns the view
     *
     * @return HasOne
     */
    public function trashedViews() : HasOne
    {
        return $this->hasOne(PageView::class, 'id', 'page_view_id')
            ->where('deleted_at', '!=', null);
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
            ->withPivot(['uuid', 'id', 'data', 'order', 'deleted_at']);
    }

    /**
     * Section Revisions
     *
     * @return HasMany
     */
    public function sectionsRevisions()
    {
        return $this->hasMany(
            PageSectionsPivot::class,
            'page_id'
        )->with(['revisionHistory' => function (MorphMany $query) {
            return $query
                ->where('old_value', '!=', null)
                ->orderBy('created_at', 'DESC');
        }]);
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
            'section_id'
        )
            ->wherePivot('deleted_at', '!=', null)
            ->withPivot(['uuid', 'id', 'data', 'deleted_at']);
    }

    /**
     * Sub Pages
     *
     * Returns the sub pages
     *
     * @return HasMany
     */
    public function subPages() : HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Parent
     *
     * Returns the page parent
     *
     * @return BelongsTo
     */
    public function parent() : BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
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
     * Get Has Sub Pages Attribute
     *
     * @return bool
     */
    public function getHasSubPagesAttribute() : bool
    {
        return $this->subPages()->count() > 0;
    }

    /**
     * Get Has Parent Page
     *
     * @return bool
     */
    public function getHasParentPageAttribute() : bool
    {
        return $this->parent()->count() > 0;
    }

    /**
     * Get Url Attribute
     *
     * Recursively creates the full url slug
     *
     * @return string
     */
    public function getUrlAttribute() : string
    {
        $string = '/';
        $page = $this;

        while ($page->has_parent_page) {
            $string .= $this->parent()->first()->slug . '/';
            $page = $this->parent()->first();
        }

        $string .= $this->slug;

        return $string;
    }

    /**
     * Get Is Dynamic Attribute
     *
     * @return bool
     */
    public function getIsDynamicAttribute() : bool
    {
        return $this->view()->first()->name === 'dynamic';
    }

    /**
     * Get Slug Attribute
     *
     * @param $value
     *
     * @return string
     */
    public function getSlugAttribute($value) : string
    {
        if ($value === 'homepage') {
            return '/';
        }

        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Set Slug Attribute
     *
     * @param $value
     */
    public function setSlugAttribute($value)
    {
        // If slug is homepage change it to a forward slash referring the the root url
        if ($value === 'homepage') {
            $this->attributes['slug'] = '/';
        } else {
            $this->attributes['slug'] = $value;
        }
    }
}

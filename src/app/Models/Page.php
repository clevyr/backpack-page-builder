<?php

namespace Clevyr\PageBuilder\app\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    /**
     * @var string[] $appends
     */
    protected $appends = [
        'has_sub_pages',
        'url',
        'is_published'
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
        return $this
            ->hasMany(self::class, 'parent_id')
            ->where('hide_on_menu', false);
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
     * Get Is Published Attribute
     *
     * @return bool
     */
    public function getIsPublishedAttribute() : bool
    {
        if (isset($this->attributes['published_at'])) {
            // Set date attribute
            $date = $this->getAttribute('published_at');

            // Check for a date
            if (!is_null($date)) {
                // Parse the date and check if it is before the current date
                if (Carbon::parse($this->attributes['published_at'])->isBefore(Carbon::now())) {
                    return true;
                }

                // Return false otherwise
                return false;
            } else {
                // Return false otherwise
                return false;
            }
        }

        return false;
    }

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

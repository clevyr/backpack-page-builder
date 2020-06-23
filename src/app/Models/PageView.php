<?php

namespace Clevyr\PageBuilder\app\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PageBuilder
 * @package Clevyr\PageBuilder\app\Models
 */
class PageView extends Model
{
    use CrudTrait;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    /**
     * @var string $table
     */
    protected $table = 'page_views';

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

    /**
     * View Options
     *
     * Returns the views as an array
     *
     * @return mixed
     */
    public function viewOptions()
    {
        $options = $this->get()
            ->mapWithkeys(function ($map) {
                return [$map->id => $map->name];
            });

        if (backpack_user()->hasRole('Page Manager')) {
            $options = $options->filter(fn ($item) => $item === 'dynamic');
        }

        return $options->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}

<?php

namespace Clevyr\PageBuilder\app\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PageBuilder
 * @package Clevyr\PageBuilder\app\Models
 */
class PageSections extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    /**
     * @var string $table
     */
    protected $table = 'page_sections';

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

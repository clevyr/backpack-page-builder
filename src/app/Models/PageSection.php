<?php

namespace Clevyr\PageBuilder\app\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Class PageBuilder
 * @package Clevyr\PageBuilder\app\Models
 */
class PageSection extends Model
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
     * @var string[] $casts
     */
    protected $casts = [
        'fields' => 'array',
    ];

    protected $appends = [
        'human_name',
        'formatted_data',
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
    public function pages()
    {
        $this->belongsToMany(
            Page::class,
            PageSectionsPivot::class,
            'section_id',
            'page_id'
        );
    }
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

    /**
     * Get Formatted Data Attribute
     *
     * Json decodes or returns the original section data
     *
     * @return mixed
     */
    public function getFormattedDataAttribute()
    {
        if (isset($this->pivot->data) && !is_null($this->pivot->data)) {
            $data = json_decode($this->pivot->data, true);

            foreach ($data as $key => $d) {
                if (is_array($d)) {
                  $data[$key] = $d;
                } else {
                  $data[$key] = json_decode($d, true) ?? $d;
                }
            }

            return $data;
        }

        return [];
    }

    /**
     * Get Human Name Attribute
     *
     * @return string
     */
    public function getHumanNameAttribute() : string
    {
        return ucwords(str_replace('_', ' ',
                Str::snake(
                    Str::camel($this->name)
                )
            )) ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}

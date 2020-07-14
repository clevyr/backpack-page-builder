<?php

namespace Clevyr\PageBuilder\app\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

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

    /**
     * Set Data Attribute
     *
     * @param $value
     */
    public function setDataAttribute($value)
    {
        $attribute_name = "data";

        // or use your own disk, defined in config/filesystems.php
        $disk = config('backpack.base.root_disk_name');

        // destination path relative to the disk above
        $destination_path = "public/uploads";

        foreach ($value as $key => $val) {
            if (is_null($val) || Str::startsWith($val, 'data:image')) {
                // Decode attribute
                $attribute = json_decode($this->attributes[$attribute_name], true);

                if ($val == null) {
                    Storage::disk($disk)->delete('public/' . $attribute[$key]);

                    $attribute[$key] = null;

                    // set null in the database column
                    $this->attributes[$attribute_name] = json_encode($attribute);
                } else if (Str::startsWith($val, 'data:image')) {
                    // 0. Make the image
                    $image = Image::make($val)->encode('jpg', 90);

                    // 1. Generate a filename.
                    $filename = md5($val . time()) . '.jpg';

                    // 2. Store the image on disk.
                    Storage::disk($disk)->put($destination_path . '/' . $filename, $image->stream());

                    // 3. Delete the previous image, if there was one.
                    Storage::disk($disk)->delete('public/' . $attribute[$key]);

                    // 4. Save the public path to the database
                    // but first, remove "public/" from the path, since we're pointing to it
                    // from the root folder; that way, what gets saved in the db
                    // is the public URL (everything that comes after the domain name)
                    $public_destination_path = Str::replaceFirst('public/', '', $destination_path);

                    $attribute[$key] = $public_destination_path . '/' . $filename;

                    $this->attributes[$attribute_name] = json_encode($attribute);
                }
            }

        }
    }
}

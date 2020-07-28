<?php

namespace Clevyr\PageBuilder\app\Observers;

use Clevyr\PageBuilder\app\Models\PageSectionsPivot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

/**
 * Class PageSectionsPivotObserver
 * @package Clevyr\PageBuilder\app\Observers
 */
class PageSectionsPivotObserver
{
    /**
     * Creating
     *
     * @param PageSectionsPivot $sectionsPivot
     */
    public function creating(PageSectionsPivot $sectionsPivot)
    {
        $sectionsPivot->setAttribute('uuid', Str::uuid());
    }

    /**
     * Saving
     *
     * @param PageSectionsPivot $sectionsPivot
     */
    public function saving(PageSectionsPivot $sectionsPivot)
    {
        $section_data = $sectionsPivot->data;
        
        if (!is_null($section_data)) {
            $image_fields = collect($sectionsPivot->section()->first()->fields)
                ->filter(function ($item) {
                    return $item['type'] === 'image';
                })
                ->map(function ($item) use ($section_data) {
                    $key = $item['name'];
                    return $section_data[$key];
                });

            if ($image_fields->count() > 0 && !is_null($sectionsPivot->data)) {
                $this->handleImageUpload($image_fields, $sectionsPivot);
            }
        }
    }

    /**
     * Handle Image Uploads
     *
     * Uploads and deletes images
     *
     * @param Collection $image_fields
     * @param PageSectionsPivot $sectionsPivot
     */
    protected function handleImageUpload($image_fields, $sectionsPivot)
    {
        // or use your own disk, defined in config/filesystems.php
        $disk = config('backpack.base.root_disk_name');

        // destination path relative to the disk above
        $destination_path = "public/uploads";

        foreach ($image_fields as $key => $val) {
            if (is_null($val) || Str::startsWith($val, 'data:image')) {
                $original = null;

                if (array_key_exists($key, $sectionsPivot->getOriginal('data'))) {
                    // Set the original image
                    $original = $sectionsPivot->getOriginal('data')[$key];
                }

                // Set the attribute to the supplied data
                $attribute = $sectionsPivot->getAttribute('data');

                // If value is null, then no image was passed and the current image
                // needs to be deleted
                if ($val === null) {
                    // Delete the image
                    Storage::disk($disk)->delete('public/' . $original);

                    // Set the image to null
                    $attribute[$key] = null;

                    // set null in the database column
                    $sectionsPivot->data = array_merge($sectionsPivot->data, $attribute);
                } else if (Str::startsWith($val, 'data:image')) {
                    // Make the image
                    $image = Image::make($val)->encode('jpg', 90);

                    // Generate a filename.
                    $filename = md5($val . time()) . '.jpg';

                    // Store the image on disk.
                    Storage::disk($disk)->put($destination_path . '/' . $filename, $image->stream());

                    // Delete the previous image
                    if (!is_null($original)) {
                        // 3. Delete the previous image, if there was one.
                        Storage::disk($disk)->delete('public/' . $original);
                    }

                    // Save the public path to the database
                    // but first, remove "public/" from the path, since we're pointing to it
                    // from the root folder; that way, what gets saved in the db
                    // is the public URL (everything that comes after the domain name)
                    $public_destination_path = Str::replaceFirst('public/', '', $destination_path);

                    // Set the attribute key to the uploaded path
                    $attribute[$key] = $public_destination_path . '/' . $filename;

                    // Update the data array
                    $sectionsPivot->data = array_merge($sectionsPivot->data, $attribute);
                }
            }
        }
    }
}

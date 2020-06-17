<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreatePagesSectionsPivotTable
 */
class CreatePagesSectionsPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages_sections_pivot', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('page_view_id');
            $table->foreign('page_view_id')->references('id')->on('page_views')->cascadeOnDelete();
            $table->unsignedInteger('section_id');
            $table->foreign('section_id')->references('id')->on('page_sections')->cascadeOnDelete();
            $table->json('data');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pages_sections_pivot');
    }
}

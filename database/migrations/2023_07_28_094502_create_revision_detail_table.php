<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevisionDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revision_detail', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->index()->unique();
            $table->text('description')->nullable();
            $table->longText('contents')->nullable();
            $table->string('language')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->foreignId('article_id');
            $table->foreignId('revision_id')
                ->references('id')
                ->on('revision')
                ->onDelete('cascade');
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
        Schema::dropIfExists('revision_detail');
    }
}

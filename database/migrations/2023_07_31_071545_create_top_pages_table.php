<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('top_pages', function (Blueprint $table) {
            $table->id();
            $table->string('organization');
            $table->string('area');
            $table->text('overview');
            $table->text('about');
            $table->text('summary');
            $table->string('cover_image')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('intro_video')->nullable();
            $table->string('official_website')->nullable();
            $table->string('fb_link')->nullable();
            $table->string('insta_link')->nullable();
            $table->enum('status', ['active', 'inactive',])->default('inactive');
            $table->foreignId('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('top_pages');
    }
}

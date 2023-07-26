<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts_detail', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->text('slug');
            $table->text('language');
            $table->foreignId('post_id')
                ->references('id')
                ->on('posts')
                ->onDelete('cascade');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts_detail');
    }
}

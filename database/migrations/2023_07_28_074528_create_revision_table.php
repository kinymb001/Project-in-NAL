<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revision', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->index()->unique();
            $table->text('description')->nullable();
            $table->longText('contents')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->enum('status', ['pending', 'approved', 'reject'])->default('pending');
            $table->string('upload_id')->nullable();
            $table->integer('user_id');
            $table->unsignedInteger('revision_number')->default(1);
            $table->foreignId('article_id')
                ->references('id')
                ->on('articles')
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
        Schema::dropIfExists('revision');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMediaTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('filename');
            $table->string('url');
            $table->string('url_xl');
            $table->string('url_lg');
            $table->string('url_md');
            $table->string('url_sm');
            $table->string('url_xs');
            $table->string('url_xxs');
            $table->string('title');
            $table->text('description');
            $table->integer('height');
            $table->integer('width');
            $table->integer('size');
            $table->string('extension');
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();
            $table->integer('user_id');
            $table->boolean('is_public')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
}

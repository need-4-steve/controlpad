<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTagsTable extends Migration
{

    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('taggable_type');
            $table->integer('taggable_id');
            $table->boolean('parent');
            $table->integer('parent_id');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tags');
    }
}

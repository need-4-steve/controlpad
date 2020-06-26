<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\CustomPage;
use Carbon\Carbon;

class AddRevisedDateCustomPages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_pages', function (Blueprint $table) {
            $table->timestamp('revised_at');
        });
        $seeder = new \UpdateCustomPages;
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_pages', function (Blueprint $table) {
            $table->dropIfExists('revised_at');
        });
    }
}

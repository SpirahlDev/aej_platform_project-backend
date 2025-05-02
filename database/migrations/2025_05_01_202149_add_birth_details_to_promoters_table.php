<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('promoters', function (Blueprint $table) {
            $table->date('birth_date');
            $table->string('birth_place', 100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promoters', function (Blueprint $table) {
            $table->dropColumn(['birth_date', 'birth_place']);
        });
    }
};

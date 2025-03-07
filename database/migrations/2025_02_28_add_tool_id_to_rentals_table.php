<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->foreignId('tool_id')->constrained()->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropForeign(['tool_id']);
            $table->dropColumn('tool_id');
        });
    }
}; 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->renameColumn('hits_property_name', 'hits_property_id');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->unsignedInteger('hits_property_id')->nullable()->change();
        });
    }

    public function down(): void
    {
    }
};

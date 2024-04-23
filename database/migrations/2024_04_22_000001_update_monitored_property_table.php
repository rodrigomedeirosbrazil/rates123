<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('monitored_properties', function (Blueprint $table) {
            $table->dropColumn('hits_property_name')->nullable();
        });
    }

    public function down(): void
    {
    }
};

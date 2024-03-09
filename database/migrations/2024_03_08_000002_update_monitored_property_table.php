<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        try {
            Schema::table('monitored_properties', function (Blueprint $table) {
                $table->integer('monitored_platform_id')->nullable();
            });
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function down(): void
    {
    }
};

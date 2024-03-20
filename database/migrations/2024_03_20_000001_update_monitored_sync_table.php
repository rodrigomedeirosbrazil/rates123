<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        try {
            Schema::table('monitored_syncs', function (Blueprint $table) {
                $table->string('status')->default('successful');
                $table->text('exception')->nullable();
            });
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }

        try {
            Schema::table('monitored_syncs', function (Blueprint $table) {
                $table->dropColumn('successful');
            });
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function down(): void
    {
        try {
            Schema::table('monitored_syncs', function (Blueprint $table) {
                $table->dropColumn('status');
                $table->dropColumn('exception');
            });
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
};

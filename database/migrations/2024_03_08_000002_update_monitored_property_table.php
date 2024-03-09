<?php

use App\Models\MonitoredPlatform;
use App\Models\MonitoredProperty;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('monitored_properties', function (Blueprint $table) {
            $table->integer('monitored_platform_id')->nullable();
        });

        Schema::table('monitored_properties', function (Blueprint $table) {
            $table->dropColumn(['capture_months_number']);
        });

        $booking = MonitoredPlatform::create([
            'name' => 'Booking',
            'slug' => 'booking',
        ]);

        MonitoredProperty::whereDeletedAt(null)
            ->update(['monitored_platform_id' => $booking->id]);
    }

    public function down(): void
    {
    }
};

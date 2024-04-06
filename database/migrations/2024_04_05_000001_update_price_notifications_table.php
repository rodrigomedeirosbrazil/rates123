<?php

use App\Managers\PriceManager;
use App\Models\PriceNotification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('price_notifications', function (Blueprint $table) {
            $table->decimal('average_price', 10, 2)->nullable();
        });

        Schema::table('price_notifications', function (Blueprint $table) {
            $table->dropColumn('change_percent');
        });

        $priceManager = new PriceManager();

        PriceNotification::query()
            ->orderBy('created_at', 'asc')
            ->cursor()
            ->each(function ($price) use ($priceManager) {
                $price->average_price = $priceManager->calculatePropertyModePrice($price->monitored_property_id);
                $price->save();
            });
    }

    public function down(): void
    {
        Schema::table('price_notifications', function (Blueprint $table) {
            $table->dropColumn('average_price');
        });
    }
};

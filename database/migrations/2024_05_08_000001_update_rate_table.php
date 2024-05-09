<?php

use App\Models\Rate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            $table->integer('min_stay')->default(1);
        });

        Rate::query()
            ->cursor()
            ->each(function (Rate $rate) {
                $rate->min_stay = data_get($rate->extra, 'minLengthOfStay') ?? data_get($rate->extra, 'minStay') ?? 1;
                $rate->extra = [];
                $rate->save();
            });
    }

    public function down(): void
    {
    }
};

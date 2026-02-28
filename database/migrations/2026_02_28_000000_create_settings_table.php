<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->string('value', 500)->nullable();
            $table->timestamps();
        });
        DB::table('settings')->insert(['key' => 'show_purchase_price', 'value' => '1']);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

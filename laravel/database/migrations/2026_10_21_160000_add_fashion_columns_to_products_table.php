<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('sizes')->nullable()->after('stock');
            $table->json('colors')->nullable()->after('sizes');
            $table->string('material')->nullable()->after('colors');
            $table->string('care_instructions')->nullable()->after('material');
            $table->string('fit_type')->nullable()->after('care_instructions');
            $table->string('gender')->nullable()->default('men')->after('fit_type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sizes', 'colors', 'material', 'care_instructions', 'fit_type', 'gender']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('photo_url')->nullable()->after('comment');
            $table->boolean('is_verified_buyer')->default(true)->after('photo_url');
            $table->integer('helpful_count')->default(0)->after('is_verified_buyer');
            $table->json('tags')->nullable()->after('helpful_count');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['photo_url', 'is_verified_buyer', 'helpful_count', 'tags']);
        });
    }
};

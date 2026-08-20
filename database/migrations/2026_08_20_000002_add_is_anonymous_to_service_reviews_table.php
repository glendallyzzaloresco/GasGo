<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('service_reviews', 'is_anonymous')) {
                $table->boolean('is_anonymous')->default(false)->after('comment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('service_reviews', 'is_anonymous')) {
                $table->dropColumn('is_anonymous');
            }
        });
    }
};

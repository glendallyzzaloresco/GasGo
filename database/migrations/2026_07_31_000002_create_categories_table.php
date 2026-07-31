<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon_class')->default('fas fa-folder');
                $table->string('color_code', 7)->default('#1a6db0');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed initial categories from existing product categories
            $initialCategories = [
                ['name' => 'LPG Tanks', 'slug' => 'lpg-tanks', 'description' => 'Main LPG tanks and cylinders', 'icon_class' => 'fas fa-fire', 'color_code' => '#1a6db0', 'is_active' => true],
                ['name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Hoses, regulators, clamps, and fittings', 'icon_class' => 'fas fa-tools', 'color_code' => '#f7941d', 'is_active' => true],
                ['name' => 'Appliances', 'slug' => 'appliances', 'description' => 'Gas stoves, burners, and appliances', 'icon_class' => 'fas fa-store', 'color_code' => '#27ae60', 'is_active' => true],
            ];

            foreach ($initialCategories as $cat) {
                DB::table('categories')->insert(array_merge($cat, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

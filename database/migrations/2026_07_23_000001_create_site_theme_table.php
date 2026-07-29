<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_theme', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary()->default(1);
            $table->string('primaryColor', 7)->default('#1a6db0');
            $table->string('accentColor', 7)->default('#f7941d');
            $table->string('backgroundColor', 7)->default('#f4f7fb');
            $table->string('sidebarBackground', 7)->default('#111b35');
            $table->string('logoUrl')->nullable();
            $table->text('footerDescription')->nullable();
            $table->text('contactAddress')->nullable();
            $table->text('contactPhone')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        DB::table('site_theme')->insert([
            'id' => 1,
            'primaryColor' => '#1a6db0',
            'accentColor' => '#f7941d',
            'backgroundColor' => '#f4f7fb',
            'sidebarBackground' => '#111b35',
            'logoUrl' => asset('images/logo-gasgo.png'),
            'footerDescription' => 'Your trusted partner for fast, reliable LPG delivery. Track your orders in real-time and earn rewards with every purchase.',
            'contactAddress' => 'PNR Site Estacion San Miguel Calasiao Pangasinan',
            'contactPhone' => '+63 912 345 6789',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_theme');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE leads MODIFY source ENUM('contact_page', 'homepage', 'chatbot', 'microsite') NOT NULL DEFAULT 'contact_page'");

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('vip_microsite_id')->nullable()->after('interested_plan_id')
                ->constrained('vip_microsites')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vip_microsite_id');
        });

        DB::statement("ALTER TABLE leads MODIFY source ENUM('contact_page', 'homepage', 'chatbot') NOT NULL DEFAULT 'contact_page'");
    }
};

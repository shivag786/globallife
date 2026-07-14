<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Product detail pages route "Enquire to Order" through the same lead
        // pipeline, tagged with a distinct source so they're identifiable in admin.
        DB::statement("ALTER TABLE leads MODIFY source ENUM('contact_page','homepage','chatbot','microsite','product') NOT NULL DEFAULT 'contact_page'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE leads MODIFY source ENUM('contact_page','homepage','chatbot','microsite') NOT NULL DEFAULT 'contact_page'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_profile_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vip_microsite_id')->constrained()->cascadeOnDelete();
            $table->enum('event_type', [
                'page_view', 'whatsapp_click', 'call_click', 'direction_click', 'website_click', 'booking_click',
            ]);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['vip_microsite_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_profile_events');
    }
};

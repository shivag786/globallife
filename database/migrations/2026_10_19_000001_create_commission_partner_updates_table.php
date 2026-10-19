<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A note left whenever Super Admin changes a Commission Partner's details.
 *
 * The partner belongs to a Branch Manager, who would otherwise find their
 * partner's commission or contact details changed with no explanation. Each row
 * records what changed (changed_fields: field => [old, new]), who changed it, an optional
 * reason, and whether that Branch Manager has seen it yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_partner_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_partner_id')->constrained('users')->cascadeOnDelete();
            // The partner's Branch Manager at the time of the change: the person
            // the note is for. Kept even if the partner later moves.
            $table->foreignId('branch_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('changed_fields');
            $table->text('note')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['branch_manager_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_partner_updates');
    }
};

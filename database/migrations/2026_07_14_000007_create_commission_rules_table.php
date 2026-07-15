<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dynamic, Super-Admin-configured product commission rules. A rule targets a
     * scope (global default, a category, or a single product) and a beneficiary
     * role, and pays either a percentage or a fixed amount. The engine resolves
     * the most-specific active rule (product > category > global) at sale time —
     * so commission levels are entirely data-driven, never hardcoded.
     */
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->enum('scope', ['global', 'category', 'product']);
            // 0 for global; the category_id or product_id otherwise.
            $table->unsignedBigInteger('scope_id')->default(0);
            $table->string('role');
            $table->enum('type', ['percent', 'fixed'])->default('percent');
            $table->decimal('value', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['scope', 'scope_id', 'role']);
            $table->index(['scope', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};

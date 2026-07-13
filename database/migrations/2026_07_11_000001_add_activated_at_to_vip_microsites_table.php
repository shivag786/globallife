<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vip_microsites', function (Blueprint $table) {
            $table->timestamp('activated_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('vip_microsites', function (Blueprint $table) {
            $table->dropColumn('activated_at');
        });
    }
};

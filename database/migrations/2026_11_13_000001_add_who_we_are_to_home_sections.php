<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the "Who We Are" homepage section: a photo with an overlaid quote card,
 * rich body text, stat chips and two buttons. The quote needs its own fields.
 */
return new class extends Migration
{
    private const OLD_TYPES = "'hero','about','features','stats','cta','founder_quote','products_showcase',"
        ."'testimonials_showcase','presence_map','business_opportunity','process_steps','vip_plans',"
        ."'blog_showcase','upcoming_events','gallery','enquiry_form','team','certifications','quality',"
        ."'category_products','products_by_category'";

    private const NEW_TYPES = self::OLD_TYPES.",'who_we_are'";

    public function up(): void
    {
        DB::statement('ALTER TABLE home_sections MODIFY type ENUM('.self::NEW_TYPES.') NOT NULL');

        Schema::table('home_sections', function (Blueprint $table) {
            $table->text('quote')->nullable()->after('content');
            $table->string('quote_author', 150)->nullable()->after('quote');
        });
    }

    public function down(): void
    {
        DB::table('home_sections')->where('type', 'who_we_are')->delete();

        Schema::table('home_sections', function (Blueprint $table) {
            $table->dropColumn(['quote', 'quote_author']);
        });

        DB::statement('ALTER TABLE home_sections MODIFY type ENUM('.self::OLD_TYPES.') NOT NULL');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_TYPES = "'hero','about','features','stats','cta','founder_quote','products_showcase',"
        ."'testimonials_showcase','presence_map','business_opportunity','process_steps','vip_plans'";

    private const NEW_TYPES = "'hero','about','features','stats','cta','founder_quote','products_showcase',"
        ."'testimonials_showcase','presence_map','business_opportunity','process_steps','vip_plans',"
        ."'blog_showcase','upcoming_events','gallery','enquiry_form'";

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE home_sections MODIFY type ENUM('.self::NEW_TYPES.') NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE home_sections MODIFY type ENUM('.self::OLD_TYPES.') NOT NULL');
    }
};

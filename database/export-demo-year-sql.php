<?php
/**
 * Export the DemoYearSeeder rows from the local DB as a re-runnable SQL import
 * for the live server (phpMyAdmin). Run from the project root:
 *   php database/export-demo-year-sql.php database/demo-year-data.sql
 */
require getcwd().'/vendor/autoload.php';
$app = require getcwd().'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\DemoYear\DemoYearData as D;
use Illuminate\Support\Facades\DB;

$out = fopen($argv[1], 'w');
$pdo = DB::connection()->getPdo();
$db = DB::connection()->getDatabaseName();
$w = fn (string $s) => fwrite($out, $s."\n");
$q = fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v);
$qs = fn (array $list) => implode(', ', array_map($q, $list));

// ------------------------------------------------------------------ who/what is "demo"
$staff = collect([...D::admins(), ...D::subAdmins(), ...D::branchManagers(), ...D::commissionPartners(), ...D::employees()])
    ->map(fn ($p) => str_replace('-', '.', $p['key']).'@'.D::STAFF_DOMAIN)->all();
$demoLike = '%@'.D::DEMO_DOMAIN;

$U = DB::table('users')->whereIn('email', $staff)->orWhere('email', 'like', $demoLike)->pluck('id')->all();
$S = DB::table('vip_microsites')->whereIn('user_id', $U)->pluck('id')->all();
$O = DB::table('orders')->whereIn('user_id', $U)->orWhereIn('fulfilled_by_microsite_id', $S)->pluck('id')->all();
$E = DB::table('employees')->whereIn('user_id', $U)->pluck('id')->all();
$assetIds = DB::table('assets')->where('asset_tag', 'like', 'GLD-%')->pluck('id')->all();
$runIds = DB::table('payslips')->whereIn('employee_id', $E)->distinct()->pluck('payroll_run_id')->all();
$months = DB::table('payroll_runs')->whereIn('id', $runIds)->pluck('month')->all();
$seeder = new ReflectionClass(Database\Seeders\DemoYearSeeder::class);
$announce = array_column((fn () => $this->announcements())->call($seeder->newInstanceWithoutConstructor()), 1);
$assetTags = DB::table('assets')->whereIn('id', $assetIds)->pluck('asset_tag')->all();

// table => [where column, ids] (rows to export), in insert order
$own = [
    'users' => ['id', $U],
    'user_profiles' => ['user_id', $U],
    'wallets' => ['user_id', $U],
    'addresses' => ['user_id', $U],
    'branch_manager_cities' => ['user_id', $U],
    'city_manager' => ['user_id', $U],
    'vip_microsites' => ['id', $S],
    'business_banners' => ['vip_microsite_id', $S],
    'business_services' => ['vip_microsite_id', $S],
    'business_gallery_items' => ['vip_microsite_id', $S],
    'business_faqs' => ['vip_microsite_id', $S],
    'business_reviews' => ['vip_microsite_id', $S],
    'business_products' => ['vip_microsite_id', $S],
    'business_settlements' => ['vip_microsite_id', $S],
    'commission_transactions' => ['vip_microsite_id', $S],
    'vip_renewals' => ['vip_microsite_id', $S],
    'orders' => ['id', $O],
    'order_items' => ['order_id', $O],
    'commission_earnings' => ['order_id', $O],
    'commission_payouts' => ['user_id', $U],
    'withdrawal_requests' => ['user_id', $U],
    'leads' => ['id', DB::table('leads')->where('email', 'like', $demoLike)->orWhereIn('vip_microsite_id', $S)->pluck('id')->all()],
    'employees' => ['id', $E],
    'employee_salaries' => ['employee_id', $E],
    'attendances' => ['employee_id', $E],
    'attendance_regularizations' => ['employee_id', $E],
    'leave_balances' => ['employee_id', $E],
    'leave_requests' => ['employee_id', $E],
    'payroll_runs' => ['id', $runIds],
    'payslips' => ['employee_id', $E],
    'salary_advances' => ['employee_id', $E],
    'expense_claims' => ['employee_id', $E],
    'assets' => ['id', $assetIds],
    'asset_assignments' => ['asset_id', $assetIds],
    'announcements' => ['title', $announce],
];

// local id => position, per own table (new id = @o_<table> + position)
$pos = [];
$rowsOf = [];
foreach ($own as $table => [$col, $ids]) {
    $rows = $ids ? DB::table($table)->whereIn($col, $ids)->orderBy(
        DB::getSchemaBuilder()->hasColumn($table, 'id') ? 'id' : $col
    )->get()->map(fn ($r) => (array) $r)->all() : [];
    $rowsOf[$table] = $rows;
    if ($rows && array_key_exists('id', $rows[0])) {
        $min = min(array_column($rows, 'id'));
        foreach ($rows as $r) {
            $pos[$table][$r['id']] = $r['id'] - $min + 1;
        }
    }
}

// foreign keys: [table][column] => referenced table
$fks = [];
foreach (DB::select('select table_name t, column_name c, referenced_table_name r from information_schema.key_column_usage where table_schema = ? and referenced_table_name is not null', [$db]) as $fk) {
    $fks[$fk->t][$fk->c] = $fk->r;
}

// shared rows, looked up by natural key on the live server
$shared = [];
$ref = function (string $table, $id) use (&$shared, $q) {
    if ($id === null) {
        return 'NULL';
    }
    $var = "@{$table}_{$id}";
    if (isset($shared[$var])) {
        return $var;
    }
    $row = (array) DB::table($table)->find($id);
    $shared[$var] = match ($table) {
        'vip_plans', 'cities', 'products', 'categories' => "(SELECT id FROM `{$table}` WHERE slug = {$q($row['slug'])} LIMIT 1)",
        'leave_types' => '(SELECT id FROM `leave_types` WHERE code = '.$q($row['code']).' LIMIT 1)',
        'shifts' => '(SELECT id FROM `shifts` ORDER BY is_default DESC, id LIMIT 1)',
        'departments' => '(SELECT id FROM `departments` WHERE name = '.$q($row['name']).' LIMIT 1)',
        'designations' => '(SELECT d.id FROM `designations` d JOIN `departments` p ON p.id = d.department_id WHERE d.name = '.$q($row['name']).' AND p.name = '.$q(DB::table('departments')->where('id', $row['department_id'])->value('name')).' LIMIT 1)',
        default => throw new RuntimeException("No natural key for {$table}"),
    };

    return $var;
};

$value = function (string $table, string $col, $v, array $row) use (&$pos, $fks, $ref, $q, $U) {
    if ($col === 'id' && isset($pos[$table])) {
        return "@o_{$table}+".$pos[$table][$v];
    }
    // Never ship files or secrets the live server cannot read.
    if ($table === 'user_profiles' && in_array($col, ['pan_number', 'aadhaar_number', 'photo_path', 'aadhaar_file_path', 'pan_file_path'], true)) {
        return 'NULL';
    }
    if ($table === 'employees' && $col === 'photo_path') {
        return 'NULL';
    }
    if ($table === 'employees' && $col === 'employee_code') {
        return "CONCAT('GL-EMP-', LPAD(CAST(@emp_code + ".(int) substr($v, -4).' AS UNSIGNED), 4, \'0\'))';
    }
    if ($v === null) {
        return 'NULL';
    }
    $target = $fks[$table][$col] ?? null;
    if ($target === 'users') {
        return in_array($v, $U) ? '@o_users+'.$pos['users'][$v] : '@super';
    }
    if ($target && isset($pos[$target])) {
        return isset($pos[$target][$v]) ? "@o_{$target}+".$pos[$target][$v] : 'NULL';
    }
    if ($target) {
        return $ref($target, $v);
    }

    return $q($v);
};

// ------------------------------------------------------------------ build the body first (it registers shared lookups)
$body = [];
foreach ($own as $table => $_) {
    $rows = $rowsOf[$table];
    if (! $rows) {
        continue;
    }
    $cols = array_keys($rows[0]);
    $body[] = "\n-- {$table} (".count($rows).')';
    foreach (array_chunk($rows, 200) as $chunk) {
        $values = array_map(fn ($r) => '('.implode(', ', array_map(fn ($c) => $value($table, $c, $r[$c], $r), $cols)).')', $chunk);
        $body[] = 'INSERT INTO `'.$table.'` (`'.implode('`, `', $cols)."`) VALUES\n".implode(",\n", $values).';';
    }
}

// Role / permission links and the page's catalogue list resolve by name/slug per row.
$links = [];
foreach (DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->where('model_type', 'App\\Models\\User')->whereIn('model_id', $U)->get(['roles.name', 'model_id']) as $r) {
    $links[] = 'INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) SELECT id, \'App\\\\Models\\\\User\', @o_users+'.$pos['users'][$r->model_id].' FROM `roles` WHERE name = '.$q($r->name)." AND guard_name = 'web';";
}
$perms = DB::table('model_has_permissions')->join('permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')->where('model_type', 'App\\Models\\User')->whereIn('model_id', $U)->get(['permissions.name', 'model_id'])->groupBy('model_id');
foreach ($perms as $uid => $list) {
    $links[] = 'INSERT INTO `model_has_permissions` (`permission_id`, `model_type`, `model_id`) SELECT id, \'App\\\\Models\\\\User\', @o_users+'.$pos['users'][$uid].' FROM `permissions` WHERE guard_name = \'web\' AND name IN ('.$qs($list->pluck('name')->all()).');';
}
foreach (DB::table('vip_products')->join('products', 'products.id', '=', 'vip_products.product_id')->whereIn('vip_microsite_id', $S)->get(['vip_products.*', 'products.slug']) as $r) {
    $links[] = 'INSERT INTO `vip_products` (`vip_microsite_id`, `product_id`, `is_visible`, `is_featured`, `display_order`, `created_at`, `updated_at`) SELECT @o_vip_microsites+'.$pos['vip_microsites'][$r->vip_microsite_id].', id, '.(int) $r->is_visible.', '.(int) $r->is_featured.', '.(int) $r->display_order.', '.$q($r->created_at).', '.$q($r->updated_at).' FROM `products` WHERE slug = '.$q($r->slug).' LIMIT 1;';
}

// ------------------------------------------------------------------ header
$w('-- One year of demo data for reports (DemoYearSeeder), as a plain SQL import for the live server.');
$w('-- Generated '.date('Y-m-d H:i').' from the local database. Dates run '.DB::table('orders')->whereIn('id', $O)->min('placed_at').' → '.DB::table('orders')->whereIn('id', $O)->max('placed_at').'.');
$w('-- Needs: all migrations, a Super Admin, the VIP plans growth / professional / growth-plus / premium,');
$w('--        cities lucknow / jhansi / pune / mumbai / hyderabad / bengaluru.');
$w('-- Photos ship with the code in storage/app/public/uploads/demo/year (deploy before importing).');
$w('-- Re-runnable: it first removes its own earlier copy (users on @'.D::DEMO_DOMAIN.' or in the staff list below) and nothing else.');
$w('-- New ids start after the current maximum of each table, so existing data is never overwritten.');
$w('-- All demo logins use the password Password!123.');
$w('SET NAMES utf8mb4;');
$w('START TRANSACTION;');
$w('');
$w('-- ===================== 1. remove an earlier copy (cascades remove pages, orders\' lines, payslips …)');
$w('DROP TEMPORARY TABLE IF EXISTS demo_users; CREATE TEMPORARY TABLE demo_users (id BIGINT UNSIGNED PRIMARY KEY);');
$w('INSERT IGNORE INTO demo_users SELECT id FROM `users` WHERE email LIKE '.$q($demoLike).' OR email IN ('.$qs($staff).');');
$w('DROP TEMPORARY TABLE IF EXISTS demo_sites; CREATE TEMPORARY TABLE demo_sites (id BIGINT UNSIGNED PRIMARY KEY);');
$w('INSERT IGNORE INTO demo_sites SELECT id FROM `vip_microsites` WHERE user_id IN (SELECT id FROM demo_users);');
$w('DELETE FROM `orders` WHERE user_id IN (SELECT id FROM demo_users) OR fulfilled_by_microsite_id IN (SELECT id FROM demo_sites);');
$w('DELETE FROM `leads` WHERE email LIKE '.$q($demoLike).' OR vip_microsite_id IN (SELECT id FROM demo_sites);');
$w('DELETE FROM `assets` WHERE asset_tag IN ('.$qs($assetTags).');');
$w('DELETE FROM `announcements` WHERE title IN ('.$qs($announce).');');
$w('DELETE FROM `payslips` WHERE employee_id IN (SELECT id FROM `employees` WHERE user_id IN (SELECT id FROM demo_users));');
$w('DELETE r FROM `payroll_runs` r LEFT JOIN `payslips` p ON p.payroll_run_id = r.id WHERE p.id IS NULL AND r.month IN ('.$qs($months).');');
$w("DELETE FROM `model_has_roles` WHERE model_type = 'App\\\\Models\\\\User' AND model_id IN (SELECT id FROM demo_users);");
$w("DELETE FROM `model_has_permissions` WHERE model_type = 'App\\\\Models\\\\User' AND model_id IN (SELECT id FROM demo_users);");
$w('DELETE FROM `users` WHERE id IN (SELECT user_id FROM `vip_microsites` WHERE id IN (SELECT id FROM demo_sites));');
$w('DELETE FROM `users` WHERE id IN (SELECT id FROM demo_users);');
$w('');
$w('-- ===================== 2. shared records the data hangs off');
$w("INSERT IGNORE INTO `roles` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES ('customer', 'web', NOW(), NOW()), ('employee', 'web', NOW(), NOW());");
$w("SET @super = (SELECT u.id FROM `users` u JOIN `model_has_roles` m ON m.model_id = u.id AND m.model_type = 'App\\\\Models\\\\User' JOIN `roles` r ON r.id = m.role_id WHERE r.name = 'super_admin' ORDER BY u.id LIMIT 1);");
foreach (D::holidays() as $date => $name) {
    $w('INSERT IGNORE INTO `holidays` (`date`, `name`, `created_at`, `updated_at`) VALUES ('.$q($date).', '.$q($name).', NOW(), NOW());');
}
foreach (D::departments() as $dept => $designations) {
    $w('INSERT INTO `departments` (`name`, `is_active`, `created_at`, `updated_at`) SELECT '.$q($dept).', 1, NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `departments` WHERE name = '.$q($dept).');');
    foreach ($designations as $d) {
        $w('INSERT INTO `designations` (`department_id`, `name`, `is_active`, `created_at`, `updated_at`) SELECT p.id, '.$q($d).', 1, NOW(), NOW() FROM `departments` p WHERE p.name = '.$q($dept).' AND NOT EXISTS (SELECT 1 FROM `designations` x WHERE x.department_id = p.id AND x.name = '.$q($d).');');
    }
}
$cats = DB::table('categories')->whereIn('slug', collect(D::catalogueProducts())->map(fn ($p) => Illuminate\Support\Str::slug($p['category']))->unique())->get();
foreach ($cats as $c) {
    $w('INSERT INTO `categories` (`name`, `slug`, `status`, `display_order`, `created_at`, `updated_at`) SELECT '.$q($c->name).', '.$q($c->slug).", 'active', 0, NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE slug = ".$q($c->slug).');');
}
foreach (DB::table('products')->whereIn('slug', array_column(D::catalogueProducts(), 'slug'))->get() as $p) {
    $p = (array) $p;
    $p['category_id'] = '(SELECT id FROM `categories` WHERE slug = '.$q(DB::table('categories')->where('id', $p['category_id'])->value('slug')).' LIMIT 1)';
    unset($p['id'], $p['brand_id']);
    $cols = array_keys($p);
    $vals = array_map(fn ($c) => $c === 'category_id' ? $p[$c] : $q($p[$c]), $cols);
    $w('INSERT INTO `products` (`'.implode('`, `', $cols).'`) SELECT '.implode(', ', $vals).' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE slug = '.$q($p['slug']).');');
}
foreach ($shared as $var => $sql) {
    $w("SET {$var} = {$sql};");
}
$w("SET @emp_code = (SELECT COALESCE(MAX(CAST(SUBSTRING(employee_code, 8) AS UNSIGNED)), 0) FROM `employees` WHERE employee_code LIKE 'GL-EMP-%');");
foreach (array_keys($pos) as $table) {
    $w("SET @o_{$table} = (SELECT COALESCE(MAX(id), 0) FROM `{$table}`);");
}
$w('');
$w('-- ===================== 3. the data (ids are offsets above the current maximum)');
$w('SET FOREIGN_KEY_CHECKS = 0;');
foreach ($body as $line) {
    $w($line);
}
$w('');
$w('-- roles, permissions, catalogue shown on each business page');
foreach ($links as $line) {
    $w($line);
}
$w('');
$w('SET FOREIGN_KEY_CHECKS = 1;');
$w('DROP TEMPORARY TABLE IF EXISTS demo_users; DROP TEMPORARY TABLE IF EXISTS demo_sites;');
$w('COMMIT;');
fclose($out);

echo 'Wrote '.$argv[1].' ('.round(filesize($argv[1]) / 1048576, 1).' MB), '.count($U).' users, '.count($O).' orders, '.count($E)." employees.\n";

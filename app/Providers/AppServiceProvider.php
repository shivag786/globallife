<?php

namespace App\Providers;

use App\Support\UploadsRoot;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admin has unrestricted access to every permission gate.
        Gate::before(function ($user, string $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        // Laravel's default pagination template is written in Tailwind, which
        // this app no longer ships; render ->links() with Bootstrap's markup.
        Paginator::useBootstrapFive();

        // Decide where uploads go on every request, not only when the config
        // file is read: a `config:cache` taken before uploads moved would
        // otherwise keep pointing them into the deploy folder, where the next
        // push deletes them. See App\Support\UploadsRoot.
        $uploadsRoot = UploadsRoot::resolve(
            config('filesystems.public_root_override'),
            $this->app->environment(),
        );

        if (config('filesystems.disks.public.root') !== $uploadsRoot) {
            config(['filesystems.disks.public.root' => $uploadsRoot]);
            Storage::forgetDisk('public');
        }
    }
}

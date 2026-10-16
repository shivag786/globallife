<?php

namespace App\Support;

/**
 * Where public uploads (product, category, banner, gallery images ...) live.
 *
 * Production deploys from git by rebuilding the deploy directory from the
 * repository, so any file the repository does not contain is gone after the
 * next push -- which is why a re-uploaded product image kept disappearing while
 * older, committed ones survived. Uploads therefore have to live OUTSIDE the
 * deploy directory.
 *
 * Resolution order:
 *   1. FILESYSTEM_PUBLIC_ROOT, if set -- an explicit absolute path always wins.
 *   2. Outside local/testing: a sibling of the deploy directory, e.g.
 *      .../domains/site/public_html-uploads beside .../domains/site/public_html.
 *      The deploy only ever rewrites public_html, so this survives every push.
 *      Created on first use; if the parent is not writable it is skipped
 *      rather than breaking every upload.
 *   3. storage/app/public inside the project (local development, and the safe
 *      fallback).
 *
 * Called from config/filesystems.php, so with `config:cache` it is resolved
 * once, at cache time.
 */
final class UploadsRoot
{
    /** The in-project location, where the committed legacy uploads still sit. */
    public static function legacy(): string
    {
        return storage_path('app/public');
    }

    public static function resolve(?string $explicit = null, ?string $environment = null, ?string $basePath = null): string
    {
        $explicit ??= env('FILESYSTEM_PUBLIC_ROOT');
        $environment ??= (string) env('APP_ENV', 'production');
        $basePath ??= base_path();

        if (is_string($explicit) && trim($explicit) !== '') {
            return rtrim($explicit, '/\\');
        }

        if (in_array($environment, ['local', 'testing'], true)) {
            return self::legacy();
        }

        $persistent = self::persistentPath($basePath);

        if (is_dir($persistent) && is_writable($persistent)) {
            return $persistent;
        }

        $parent = dirname($persistent);

        if (! is_dir($persistent) && is_dir($parent) && is_writable($parent) && @mkdir($persistent, 0755, true)) {
            return $persistent;
        }

        // Could not set up the persistent folder: keep uploads working where
        // they always have, rather than failing every upload.
        return self::legacy();
    }

    /** The sibling-of-the-deploy-directory location used in production. */
    public static function persistentPath(string $basePath): string
    {
        $basePath = rtrim($basePath, '/\\');

        return dirname($basePath).DIRECTORY_SEPARATOR.basename($basePath).'-uploads';
    }
}

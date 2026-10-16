<?php

namespace App\Support;

/**
 * Where public uploads (product, category, banner, gallery images ...) live.
 *
 * Production deploys from git by rebuilding the deploy directory from the
 * repository, so any file the repository does not contain is gone after the
 * next push -- which is why re-uploaded product images kept disappearing while
 * older, committed ones survived. Uploads therefore live OUTSIDE the deploy
 * directory.
 *
 * Resolution order:
 *   1. FILESYSTEM_PUBLIC_ROOT, if set -- an explicit absolute path always wins.
 *   2. Except under the test suite: a sibling of the deploy directory, e.g.
 *      .../domains/site/public_html-uploads beside .../domains/site/public_html.
 *      Created on first use. Deliberately NOT tied to APP_ENV: a server whose
 *      .env still says APP_ENV=local would otherwise keep losing uploads.
 *   3. storage/app/public inside the project -- the test suite, and the
 *      fallback if the sibling folder cannot be created (rather than breaking
 *      every upload).
 *
 * The root is applied at RUNTIME by AppServiceProvider, not only in
 * config/filesystems.php, so a stale `config:cache` on the server cannot pin
 * uploads to the old folder.
 */
final class UploadsRoot
{
    /** The in-project location, where the committed legacy uploads still sit. */
    public static function legacy(): string
    {
        return storage_path('app/public');
    }

    public static function resolve(?string $explicit, ?string $environment, ?string $basePath = null): string
    {
        $basePath ??= base_path();

        if (is_string($explicit) && trim($explicit) !== '') {
            return rtrim($explicit, '/\\');
        }

        if ($environment === 'testing') {
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

        return self::legacy();
    }

    /** The sibling-of-the-deploy-directory location. */
    public static function persistentPath(string $basePath): string
    {
        $basePath = rtrim($basePath, '/\\');

        return dirname($basePath).DIRECTORY_SEPARATOR.basename($basePath).'-uploads';
    }

    /**
     * A plain report of where uploads are going and whether that is safe, for
     * the Site Settings page -- so this can be checked from a browser, without
     * shell access to the server.
     *
     * @return array{root: string, safe: bool, reason: string, writable: bool, explicit: bool, environment: string, deploy_dir: string, config_cached: bool, php: string}
     */
    public static function diagnose(string $root, ?string $explicit, string $environment): array
    {
        $deployDir = rtrim(base_path(), '/\\');
        $realRoot = realpath($root) ?: $root;
        $realDeploy = realpath($deployDir) ?: $deployDir;

        $inside = str_starts_with(
            rtrim(str_replace('\\', '/', $realRoot), '/').'/',
            rtrim(str_replace('\\', '/', $realDeploy), '/').'/'
        );

        $writable = is_dir($root) && is_writable($root);

        $reason = match (true) {
            ! $writable => 'The uploads folder does not exist or cannot be written to, so uploads will fail.',
            $inside => 'Uploads are inside the deploy folder, so every push deletes anything uploaded since the last commit. Set FILESYSTEM_PUBLIC_ROOT in .env to a folder outside it.',
            default => 'Uploads are outside the deploy folder, so pushes cannot delete them.',
        };

        return [
            'root' => $realRoot,
            'safe' => $writable && ! $inside,
            'reason' => $reason,
            'writable' => $writable,
            'explicit' => is_string($explicit) && trim($explicit) !== '',
            'environment' => $environment,
            'deploy_dir' => $realDeploy,
            'config_cached' => app()->configurationIsCached(),
            'php' => PHP_VERSION,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Pubvana\Admin;

use Composer\Script\Event;

class ComposerScripts
{
    /**
     * Mirror admin assets from the package into public/assets/admin/.
     *
     * Called by Composer post-install-cmd / post-update-cmd.
     * Mirrors the full assets/ tree — files removed from the source
     * are removed from the target on the next run.
     */
    public static function publishAssets(Event $event): void
    {
        $packageDir = dirname(__DIR__);
        $source = $packageDir . '/assets';
        $projectRoot = dirname($packageDir, 3); // vendor/pubvana/admin → project root
        $target = $projectRoot . '/public/assets/admin';

        if (!is_dir($source)) {
            return;
        }

        // Mirror: copy source → target, then prune target files not in source
        self::mirrorDirectory($source, $target);
        self::pruneOrphans($source, $target);

        $event->getIO()->write('<info>pubvana/admin:</info> assets published to public/assets/admin/');
    }

    /**
     * Recursively copy source files to target, creating directories as needed.
     */
    private static function mirrorDirectory(string $source, string $target): void
    {
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative = substr($item->getPathname(), strlen($source) + 1);
            $dest = $target . '/' . $relative;

            if ($item->isDir()) {
                if (!is_dir($dest)) {
                    mkdir($dest, 0755, true);
                }
            } else {
                // Only copy if source is newer or target doesn't exist
                if (!file_exists($dest) || filemtime($item->getPathname()) > filemtime($dest)) {
                    copy($item->getPathname(), $dest);
                }
            }
        }
    }

    /**
     * Remove files/dirs in target that no longer exist in source.
     */
    private static function pruneOrphans(string $source, string $target): void
    {
        if (!is_dir($target)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($target, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            $relative = substr($item->getPathname(), strlen($target) + 1);
            $sourceEquivalent = $source . '/' . $relative;

            if ($item->isDir()) {
                if (!is_dir($sourceEquivalent)) {
                    rmdir($item->getPathname());
                }
            } else {
                if (!file_exists($sourceEquivalent)) {
                    unlink($item->getPathname());
                }
            }
        }
    }
}

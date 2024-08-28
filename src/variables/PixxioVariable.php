<?php
/**
 * HOMM pixx.io plugin for Craft CMS
 *
 * Craft CMS pixx.io adapter
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2024 HOMM interactive
 */

namespace homm\hommpixxio\variables;

use homm\hommpixxio\HOMMPixxio;

class PixxioVariable
{
    /**
     * Get the directory tree
     *
     * @param  ?int $parentID
     * @return array
     */
    public function directories(?int $parentID = null): array
    {
        return HOMMPixxio::$plugin->pixxioService->getDirectoryTree($parentID);
    }
    /**
     * Get the files from a directory
     *
     * @param  int $directoryID
     * @return array
     */
    public function files(int $directoryID): array
    {
        return HOMMPixxio::$plugin->pixxioService->getFiles($directoryID);
    }
}

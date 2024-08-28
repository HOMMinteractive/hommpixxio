<?php
/**
 * HOMM pixx.io plugin for Craft CMS
 *
 * Craft CMS pixx.io adapter
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2024 HOMM interactive
 */

namespace homm\hommpixxio\services;

use Craft;
use craft\base\Component;
use craft\helpers\StringHelper;
use homm\hommpixxio\api\PixxioClient;

/**
 * Class PixxioService
 *
 * @author    Benjamin Ammann
 * @package   HOMMPixxio
 * @since     0.0.1
 */
class PixxioService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Get the directory tree
     *
     * @param  ?int $parentID
     * @return array
     */
    public function getDirectoryTree(?int $parentID = null)
    {
        $response = (new PixxioClient())->getDirectoryTree($parentID);

        return json_decode($response->getBody())?->children ?? [];
    }

    /**
     * Get the files from a directory
     *
     * @param  int $directoryID
     * @param  int $page
     * @return array
     */
    public function getFiles(int $directoryID, int $page = 1)
    {
        $response = (new PixxioClient())->getFiles($directoryID, $page);

        /** @var \stdClass $payload */
        $payload = json_decode($response->getBody());
        $payload->pageQuantity = count($payload->files);
        $payload->pageSize = PixxioClient::FILE_PAGE_SIZE;
        $payload->pageStart = ($page * $payload->pageSize) - ($payload->pageSize - 1);
        $payload->pageEnd = ($payload->pageStart - 1) + $payload->pageQuantity;
        $payload->currentPage = $page;
        $payload->lastPage = ceil($payload->quantity / $payload->pageSize);

        return $payload ?? [];
    }

    /**
     * Search all files for $term
     *
     * @param  string $term
     * @param  int    $page
     * @param  int    $directoryID
     * @return array
     */
    public function searchFiles(string $term, int $page = 1, ?int $directoryID = null)
    {
        $response = (new PixxioClient())->searchFiles($term, $page, $directoryID);

        /** @var \stdClass $payload */
        $payload = json_decode($response->getBody());
        $payload->pageQuantity = count($payload->files);
        $payload->pageSize = PixxioClient::FILE_PAGE_SIZE;
        $payload->pageStart = ($page * $payload->pageSize) - ($payload->pageSize - 1);
        $payload->pageEnd = ($payload->pageStart - 1) + $payload->pageQuantity;
        $payload->currentPage = $page;
        $payload->lastPage = ceil($payload->quantity / $payload->pageSize);

        return $payload ?? [];
    }
}

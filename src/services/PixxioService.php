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
     * Fetch files from a directory via cursor pagination.
     *
     * @param int         $directoryID
     * @param string|null $pageCursor previous cursor, or null for first page
     * @param int         $pageSize   items per page
     * @return array
     */
    public function getFiles(int $directoryID, ?string $pageCursor = null, int $pageSize = PixxioClient::FILE_PAGE_SIZE)
    {
        $client = new PixxioClient();
        $response = $client->getFiles($directoryID, $pageCursor, $pageSize);

        /** @var \stdClass $payload */
        $payload = json_decode($response->getBody());

        if (isset($payload->cursor)) {
            $payload->nextCursor = $payload->cursor;
        }

        return $payload ?? [];
    }

    /**
     * Get a specific file binary
     */
    public function getFile(int $fileID)
    {
        return (new PixxioClient())->getFile($fileID);
    }

    /**
     * Search files by term, cursor pagination only.
     *
     * @param string      $term
     * @param ?int        $directoryID
     * @param string|null $pageCursor
     * @param int         $pageSize
     * @return array
     */
    public function searchFiles(string $term, ?int $directoryID = null, ?string $pageCursor = null, int $pageSize = PixxioClient::FILE_PAGE_SIZE)
    {
        $client = new PixxioClient();
        $response = $client->searchFiles($term, $directoryID, $pageCursor, $pageSize);

        /** @var \stdClass $payload */
        $payload = json_decode($response->getBody());

        if (isset($payload->cursor)) {
            $payload->nextCursor = $payload->cursor;
        }

        return $payload ?? [];
    }
}

<?php

/**
 * HOMM pixx.io plugin for Craft CMS
 *
 * Craft CMS pixx.io adapter
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2024 HOMM interactive
 */

namespace homm\hommpixxio\api;

use homm\hommpixxio\HOMMPixxio;

/**
 * @author    Benjamin Ammann
 * @package   HOMMPixxio
 * @since     0.0.1
 */
class PixxioClient extends \GuzzleHttp\Client
{
    public const API_PATH = '/api/v1';

    public const FILE_PAGE_SIZE = 50;

    public function __construct(array $config = [])
    {
        if (!HOMMPixxio::$plugin->getSettings()->mediaspaceUrl) {
            throw new \Exception('No pixx.io mediaspace URL defined. Please specify one in the plugin settings', 1);
        }

        if (!HOMMPixxio::$plugin->getSettings()->apiKey) {
            throw new \Exception('No pixx.io API key defined. Please specify one in the plugin settings', 1);
        }

        $config['base_uri'] = trim(HOMMPixxio::$plugin->getSettings()->mediaspaceUrl, '/') . self::API_PATH;
        $config['headers'] = ['Authorization' => 'Bearer ' . HOMMPixxio::$plugin->getSettings()->apiKey];
        parent::__construct($config);
    }

    /**
     * Get the directory tree
     *
     * @param  ?int $parentID
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getDirectoryTree(?int $parentID = null): \Psr\Http\Message\ResponseInterface
    {
        return $this->get('directories/tree', [
            'query' => [
                'parentID' => $parentID,
                'responseFields' => json_encode([
                    'id',
                    'name',
                    'parentID',
                ])
            ],
        ]);
    }

    /**
     * Get the files from a directory
     *
     * @param  int $directoryID
     * @param  int $page
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getFiles(int $directoryID, $page): \Psr\Http\Message\ResponseInterface
    {
        return $this->get('files', [
            'query' => [
                'page' => $page,
                'pageSize' => self::FILE_PAGE_SIZE,
                'filter' => json_encode([
                    'filterType' => 'connectorAnd',
                    'filters' => [
                        [
                            'filterType' => 'directory',
                            'directoryID' => $directoryID,
                        ],
                    ],
                ]),
                'responseFields' => json_encode([
                    'id',
                    'fileName',
                    'originalFileURL',
                    'previewFileURL',
                    'directory',
                ]),
            ],
        ]);
    }

    /**
     * Search all files for $term
     *
     * @param  string  $term
     * @param  int     $page
     * @param  int     $directoryID
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function searchFiles(string $term, int $page = 1, int $directoryID = null): \Psr\Http\Message\ResponseInterface
    {
        $filters = [];
        $filters[] = [
            'filterType' => 'fileName',
            'term' => $term,
            'exactMatch' => false,
            'useSynonyms' => true,
            'inverted' => false,
        ];

        if ($directoryID) {
            $filters[] = [
                'filterType' => 'directory',
                'directoryID' => $directoryID,
                'includeSubdirectories' => true,
            ];
        }

        return $this->get('files', [
            'query' => [
                'page' => $page,
                'pageSize' => self::FILE_PAGE_SIZE,
                'filter' => json_encode([
                    'filterType' => 'connectorAnd',
                    'filters' => $filters,
                ]),
                'responseFields' => json_encode([
                    'id',
                    'fileName',
                    'originalFileURL',
                    'previewFileURL',
                    'directory',
                ]),
            ],
        ]);
    }
}

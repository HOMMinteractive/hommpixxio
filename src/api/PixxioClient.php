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
        if (!HOMMPixxio::$plugin->getSettings()->getMediaspaceUrl()) {
            throw new \Exception('No pixx.io mediaspace URL defined. Please specify one in the plugin settings', 1);
        }

        if (!HOMMPixxio::$plugin->getSettings()->getApiKey()) {
            throw new \Exception('No pixx.io API key defined. Please specify one in the plugin settings', 1);
        }

        $config['base_uri'] = trim(HOMMPixxio::$plugin->getSettings()->getMediaspaceUrl(), '/') . self::API_PATH;
        $config['headers'] = ['Authorization' => 'Bearer ' . HOMMPixxio::$plugin->getSettings()->getApiKey()];
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
                    'previewFileURL',
                    'directory',
                ]),
            ],
        ]);
    }

    /**
     * Get a binary file
     *
     * @param  int $fileID
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getFile(int $fileID): \Psr\Http\Message\ResponseInterface
    {
        return $this->get('files/' . $fileID . '/convert?downloadType=original&responseType=binary');
    }

    /**
     * Search all files for $term
     *
     * @param  string  $term
     * @param  int     $page
     * @param  ?int    $directoryID
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function searchFiles(string $term, int $page = 1, ?int $directoryID = null): \Psr\Http\Message\ResponseInterface
    {
        $terms = explode(' ', $term);

        $filters = [];

        foreach ($terms as $term) {
            if (empty(trim($term))) {
                continue;
            }

            $filter = [
                'filterType' => 'fileName',
                'term' => $term,
                'exactMatch' => false,
                'useSynonyms' => true,
                'inverted' => false,
            ];

            $termCleaned = transliterator_transliterate('Any-Latin; Latin-ASCII', $term);
            if ($term !== $termCleaned) {
                $filterUncleaned = $filter;

                $filterCleaned = [
                    'filterType' => 'fileName',
                    'term' => $termCleaned,
                    'exactMatch' => false,
                    'useSynonyms' => true,
                    'inverted' => false,
                ];

                $filter = [
                    'filterType' => 'connectorOr',
                    'filters' => [$filterUncleaned, $filterCleaned],
                ];
            }

            $filters[] = $filter;
        }

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
                    'previewFileURL',
                    'directory',
                ]),
            ],
        ]);
    }
}

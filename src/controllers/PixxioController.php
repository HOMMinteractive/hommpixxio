<?php

/**
 * HOMM pixx.io plugin for Craft CMS
 *
 * Craft CMS pixx.io adapter
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2024 HOMM interactive
 */

namespace homm\hommpixxio\controllers;

use Craft;
use craft\web\Controller;
use homm\hommpixxio\HOMMPixxio;
use homm\hommpixxio\api\PixxioClient;

/**
 * @author    Benjamin Ammann
 * @package   HOMMPixxio
 * @since     0.0.1
 */
class PixxioController extends Controller
{
    protected array|bool|int $allowAnonymous = ['file'];

    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionDirectories(?int $parentID = null)
    {
        return $this->asJson(HOMMPixxio::$plugin->pixxioService->getDirectoryTree($parentID));
    }

    /**
     * @return mixed
     */
    public function actionFiles(int $directoryID)
    {
        $pageCursor = Craft::$app->request->getQueryParam('pageCursor', null);
        $pageSize = Craft::$app->request->getQueryParam('pageSize', PixxioClient::FILE_PAGE_SIZE);

        return $this->asJson(HOMMPixxio::$plugin->pixxioService->getFiles($directoryID, $pageCursor, $pageSize));
    }

    /**
     * @return mixed
     */
    public function actionSearchFiles()
    {
        $term = Craft::$app->request->getQueryParam('term', null);
        $directoryID = Craft::$app->request->getQueryParam('directoryID', null);
        $pageCursor = Craft::$app->request->getQueryParam('pageCursor', null);
        $pageSize = Craft::$app->request->getQueryParam('pageSize', PixxioClient::FILE_PAGE_SIZE);

        if (!$term) {
            return $this->asJson([]);
        }

        return $this->asJson(HOMMPixxio::$plugin->pixxioService->searchFiles($term, $directoryID, $pageCursor, $pageSize));
    }

    /**
     * @return mixed
     */
    public function actionFile(int $fileID)
    {
        $pixxioResponse = HOMMPixxio::$plugin->pixxioService->getFile($fileID);

        // Get the content, headers, and status
        $body = $pixxioResponse->getBody();
        $statusCode = $pixxioResponse->getStatusCode();
        $headers = $pixxioResponse->getHeaders();

        // Set response component via Craft
        $response = Craft::$app->getResponse();
        $response->format = \yii\web\Response::FORMAT_RAW;
        $response->statusCode = $statusCode;

        // Set content
        $response->content = $body->getContents();

        // Set mime type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($response->content);
        $response->headers->remove('Content-Type');
        $response->headers->set('Content-Type', $mimeType);

        // Set content disposition
        $contentDisposition = $headers['Content-Disposition'][0] ?? 'inline';
        $response->headers->set('Content-Disposition', str_replace('attachment', 'inline', $contentDisposition));

        return $response;
    }
}

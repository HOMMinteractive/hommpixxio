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

/**
 * @author    Benjamin Ammann
 * @package   HOMMPixxio
 * @since     0.0.1
 */
class PixxioController extends Controller
{
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
        $page = Craft::$app->request->getQueryParam('page', 1);

        return $this->asJson(HOMMPixxio::$plugin->pixxioService->getFiles($directoryID, $page));
    }

    /**
     * @return mixed
     */
    public function actionSearchFiles()
    {
        $term = Craft::$app->request->getQueryParam('term', null);
        $directoryID = Craft::$app->request->getQueryParam('directoryID', null);
        $page = Craft::$app->request->getQueryParam('page', 1);

        if (!$term) {
            return $this->asJson([]);
        }

        return $this->asJson(HOMMPixxio::$plugin->pixxioService->searchFiles($term, $page, $directoryID));
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

        // Set headers
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $response->headers->add($name, $value);
            }
        }

        // Set content
        $response->content = $body->getContents();

        // Set mime type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($response->content);
        $response->headers->remove('Content-Type');
        $response->headers->set('Content-Type', $mimeType);

        // Set content disposition
        $response->headers->remove('Content-Disposition');
        $response->headers->set('Content-Disposition', 'inline');

        return $response;
    }
}

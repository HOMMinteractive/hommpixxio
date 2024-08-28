<?php
/**
 * HOMM pixx.io plugin for Craft CMS
 *
 * Craft CMS pixx.io adapter
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2024 HOMM interactive
 */

namespace homm\hommpixxio\assetbundles\hommpixxio;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class HOMMPixxioAsset
 *
 * @author    Benjamin Ammann
 * @package   HOMMPixxio
 * @since     0.0.1
 */
class HOMMPixxioAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = "@homm/hommpixxio/assetbundles/hommpixxio/dist";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/HOMMPixxioField.js',
        ];

        $this->css = [
            'css/HOMMPixxioField.css',
        ];

        parent::init();
    }
}

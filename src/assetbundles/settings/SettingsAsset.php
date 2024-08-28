<?php
/**
 * HOMM pixx.io plugin for Craft CMS
 *
 * Craft CMS pixx.io adapter
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2024 HOMM interactive
 */

namespace homm\hommpixxio\assetbundles\settings;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Benjamin Ammann
 * @package   HOMMPixxio
 * @since     0.0.1
 */
class SettingsAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@homm/hommpixxio/assetbundles/settings/dist";

        $this->depends = [
            CpAsset::class,
        ];

        parent::init();
    }
}

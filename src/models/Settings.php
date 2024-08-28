<?php
/**
 * HOMM pixx.io plugin for Craft CMS
 *
 * Craft CMS pixx.io adapter
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2024 HOMM interactive
 */

namespace homm\hommpixxio\models;

use craft\base\Model;

/**
 * Class Settings
 *
 * @author    Benjamin Ammann
 * @package   HOMMPixxio
 * @since     0.0.1
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string pixx.io API key
     */
    public string $apiKey = '';

    /**
     * @var string pixx.io Mediaspace URL
     */
    public string $mediaspaceUrl = '';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['apiKey'], 'string'],
            [['mediaspaceUrl'], 'string'],
        ];
    }
}

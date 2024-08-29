<?php

/**
 * HOMM pixx.io plugin for Craft CMS
 *
 * Craft CMS pixx.io adapter
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2024 HOMM interactive
 */

namespace homm\hommpixxio\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\elements\Asset;
use craft\helpers\Json;
use homm\hommpixxio\assetbundles\hommpixxio\HOMMPixxioAsset;
use homm\hommpixxio\HOMMPixxio;
use yii\db\Schema;

/**
 * Class HOMMPixxioField
 *
 * @author    Benjamin Ammann
 * @package   HOMMPixxio
 * @since     0.0.1
 */
class HOMMPixxioField extends Field implements PreviewableFieldInterface
{
    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('hommpixxio', 'pixx.io');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue(mixed $value, ?\craft\base\ElementInterface $element = null): mixed
    {
        if (gettype($value) == 'string') {
            $json = array_filter(json_decode($value, true));

            if (empty($json)) {
                return null;
            }

            return $json;
        } elseif ($value === null) {
            return $value;
        } else {
            return json_encode($value);
        }
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml(mixed $value, ?\craft\base\ElementInterface $element = null): string
    {
        // Register our asset bundle
        Craft::$app->getView()->registerAssetBundle(HOMMPixxioAsset::class);

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Variables to pass down to our field JavaScript to let it namespace properly
        $jsonVars = [
            'id' => $id,
            'name' => $this->handle,
            'namespace' => $namespacedId,
            'prefix' => Craft::$app->getView()->namespaceInputId(''),
        ];
        $jsonVars = Json::encode($jsonVars);
        Craft::$app->getView()->registerJs("$('#{$namespacedId}-field').HOMMPixxioField(" . $jsonVars . ");");

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'hommpixxio/_components/fields/HOMMPixxioField_input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
                'directories' => HOMMPixxio::$plugin->pixxioService->getDirectoryTree(),
                // 'files' => HOMMPixxio::$plugin->pixxioService->getFiles(),
            ]
        );
    }

    public function getTableAttributeHtml(mixed $value, ElementInterface $element): string
    {
        return <<<EOL
            <div class="element small hasthumb" title="{$value['name']}">
                <div class="elementthumb">
                    <img srcset="{$value['url']}" alt="{$value['name']}">
                </div>
                <div class="label" style="max-width: 200px; line-height: 1.25;">
                    <span class="title">
                        <a href="{$value['url']}">{$value['name']}</a>
                    </span>
                </div>
            </div>
        EOL;
    }
}

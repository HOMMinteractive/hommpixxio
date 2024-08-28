<?php
/**
 * HOMM pixx.io plugin for Craft CMS
 *
 * Craft CMS pixx.io adapter
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2024 HOMM interactive
 */

namespace homm\hommpixxio;

use homm\hommpixxio\fields\HOMMPixxioField;

use Craft;
use craft\base\Plugin;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use homm\hommpixxio\services\PixxioService;
use homm\hommpixxio\models\Settings;
use homm\hommpixxio\variables\PixxioVariable;
use yii\base\Event;

/**
 * Class HOMMPixxio
 *
 * @author    Benjamin Ammann
 * @package   HOMMPixxio
 * @since     0.0.1
 *
 * @property  PixxioService $pixxioService
 */
class HOMMPixxio extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * HOMMPixxio::$plugin
     *
     * @var HOMMPixxio
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public bool $hasCpSettings = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'pixxioService' => PixxioService::class,
        ]);

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['hommpixxio/directories'] = 'hommpixxio/pixxio/directories';
                $event->rules['hommpixxio/directories/<parentID:\d+>'] = 'hommpixxio/pixxio/directories';
                $event->rules['hommpixxio/directories/<directoryID:\d+>/files'] = 'hommpixxio/pixxio/files';
                $event->rules['hommpixxio/files/search'] = 'hommpixxio/pixxio/search-files';
            }
        );

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = HOMMPixxioField::class;
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('pixxio', PixxioVariable::class);
            }
        );

        Craft::info(
            Craft::t(
                'hommpixxio',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate(
            'hommpixxio/settings',
            [
                'settings' => $this->getSettings(),
                'volumes' => Craft::$app->getVolumes(),
            ]
        );
    }
}

<?php
/**
 * craft-single-sign-on plugin for Craft CMS 3.x
 *
 * Craft Single Sign-On OAuth & OpenID Connect plugin allows unlimited sso / login ( Single Sign On ) with your Azure AD, Discord, G Suite / Google Apps or other custom OAuth 2.0, OpenID Connect providers.
 *
 * @link      https://github.com/miniorangedev
 * @copyright Copyright (c) 2022 miniorange
 */

namespace miniorangedev\craftsinglesignon;

use miniorangedev\craftsinglesignon\services\App as AppService;
use miniorangedev\craftsinglesignon\variables\CraftsinglesignonVariable;
use miniorangedev\craftsinglesignon\twigextensions\CraftsinglesignonTwigExtension;
use miniorangedev\craftsinglesignon\models\Settings;
use miniorangedev\craftsinglesignon\fields\Option as OptionField;
use miniorangedev\craftsinglesignon\utilities\Config as ConfigUtility;
use miniorangedev\craftsinglesignon\widgets\Settings as SettingsWidget;
use miniorangedev\craftsinglesignon\controllers\ResourcesController;
use craft\elements\User;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Fields;
use craft\services\Utilities;
use craft\web\twig\variables\CraftVariable;
use craft\services\Dashboard;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    miniorange
 * @package   Craftsinglesignon
 * @since     1.0.0
 *
 * @property  AppService $app
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class Craftsinglesignon extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Craftsinglesignon::$plugin
     *
     * @var Craftsinglesignon
     */
    public static Plugin $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public bool $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Craftsinglesignon::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        // Add in our Twig extensions
        Craft::$app->view->registerTwigExtension(new CraftsinglesignonTwigExtension());

        // Register our site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $data = ResourcesController::actionDatadb('oauthsettings');
                if(isset($data['callback_url']) && isset(explode(UrlHelper::siteUrl(), $data['callback_url'])[1])){
                    $url = explode(UrlHelper::siteUrl(), $data['callback_url'])[1];
                    $event->rules[$url] = 'craft-single-sign-on/login/callback';
                }
                $event->rules['mologin/login'] = 'craft-single-sign-on/login';
                $event->rules['mosinglesignon/create'] = 'craft-single-sign-on/settings/create';
                $event->rules['mosinglesignon/check'] = 'craft-single-sign-on/settings/check';
                $event->rules['mosinglesignon/delete'] = 'craft-single-sign-on/settings/delete';
                $event->rules['mosinglesignon/issuer'] = 'craft-single-sign-on/method/saml';
                $event->rules['mosinglesignon/samllogin'] = 'craft-single-sign-on/method/samllogin';
                $event->rules['mosinglesignon/message'] = 'craft-single-sign-on/settings/message';
            }
        );

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            [self::class, 'onRegisterCpUrlRules'],
            function (RegisterUrlRulesEvent $event) {
                
            }
        );

        // Register our fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = OptionField::class;
            }
        );

        // Register our utilities
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ConfigUtility::class;
            }
        );

        // Register our widgets
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = SettingsWidget::class;
            }
        );

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('craftsinglesignon', CraftsinglesignonVariable::class);
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    
                }
            }
        );

        // Do something after we're uninstalled
        Event::on(
            Plugins::class,
            Plugins::EVENT_BEFORE_UNINSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    self::getNotify();
                }
            }
        );

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'craft-single-sign-on',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel(): ?craft\base\Model
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'craft-single-sign-on/settings',
            [
                'settings' => Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('craft-single-sign-on'))->send()
            ]
        );
    }

    /**
     * @param RegisterUrlRulesEvent $event
     */
    public static function onRegisterCpUrlRules(RegisterUrlRulesEvent $event)
    {
        if(Craft::$app->getUser()->getIdentity()==null){
            
            if (\Craft::$app->getIsLive()) {
                $event->rules = array_merge(
                    $event->rules,
                    [
                        'login' => 'craft-single-sign-on/settings',
                    ]
                );
            }

        }else{
            $event->rules['craft-single-sign-on'] = 'craft-single-sign-on/settings/providers';
            $event->rules['craft-single-sign-on/oauth-settings'] = 'craft-single-sign-on/settings/oauthsettings';
            $event->rules['craft-single-sign-on/oauth-attribute'] = 'craft-single-sign-on/settings/oauthattribute';
            $event->rules['craft-single-sign-on/saml-settings'] = 'craft-single-sign-on/settings/samlsettings';
            $event->rules['craft-single-sign-on/saml-attribute'] = 'craft-single-sign-on/settings/samlattribute';
            $event->rules['craft-single-sign-on/saml-provider'] = 'craft-single-sign-on/settings/samlprovider';
            $event->rules['craft-single-sign-on/custom-settings'] = 'craft-single-sign-on/settings/customsettings';
            $event->rules['craft-single-sign-on/jwt-settings'] = 'craft-single-sign-on/settings/jwtsettings';
        }
    }

    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();
        $item['badgeCount'] = 5;
        $item['subnav'] = [
            'custom_provider' => ['label' => 'Providers', 'url' => 'craft-single-sign-on'],
            'custom_settings' => ['label' => 'Settings', 'url' => 'craft-single-sign-on/custom-settings'],
        ];
        return $item;
    }

    public function getNotify()
    { 
        Craft::$app->runAction('craft-single-sign-on/settings/deactivation');
    }

}

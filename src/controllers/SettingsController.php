<?php
/**
 * craft-single-sign-on plugin for Craft CMS 3.x
 *
 * Craft Single Sign-On OAuth & OpenID Connect plugin allows unlimited sso / login ( Single Sign On ) with your Azure AD, Discord, G Suite / Google Apps or other custom OAuth 2.0, OpenID Connect providers.
 *
 * @link      https://github.com/miniorangedev
 * @copyright Copyright (c) 2022 miniorange
 */

namespace miniorangedev\craftsinglesignon\controllers;

use miniorangedev\craftsinglesignon\Craftsinglesignon;
use craft\elements\User;
use miniorangedev\craftsinglesignon\controllers\MethodController;
use miniorangedev\craftsinglesignon\controllers\ResourcesController;
use miniorangedev\craftsinglesignon\utilities\Utilities;
use craft\helpers\UrlHelper;

use Craft;
use craft\web\Controller;
use yii\web\Response;

/**
 * Settings Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    miniorange
 * @package   Craftsinglesignon
 * @since     1.0.0
 */
class SettingsController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected array|int|bool $allowAnonymous = ['index', 'check', 'delete', 'save', 'oauthsettings', 'oauthattribute', 'samlprovider', 'samlsettings', 'samlattribute', 'customsettings'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/single-sign-on/settings
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $settings = (ResourcesController::actionDatadb() != null)?ResourcesController::actionDatadb():Craftsinglesignon::$plugin->getSettings();

        return $this->renderTemplate(
            'craft-single-sign-on/view/login', [
                'providers' => $settings,
            ]
        );
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/single-sign-on/settings/do-something
     *
     * @return mixed
     */


    public function actionCheck()
    {
        return dd(User::find()->all());
    }

    public static function actionCakdd($adff, $user_info)
    {
        return MethodController::actionXhjsdop($user_info, $adff);
    }

    public function actionDelete()
    {
        $email = Craft::$app->request->getQueryParam('email');
        $user_info = User::find()->email($email)->all();

        if(isset($user_info[0]["admin"]) && $user_info[0]["admin"] == 1 ){
            exit('No Email Address Return!');
        }
        
        if(isset($user_info[0])){
            $var = Craft::$app->getElements()->deleteElement($user_info[0], false);
            exit($var);
        }else{
            exit("No Customer Exists");
        }
    }

    public function actionProviders(): Response
    {   
        $settings = (ResourcesController::actionDatadb() != null)?ResourcesController::actionDatadb():Craftsinglesignon::$plugin->getSettings();

        return $this->renderTemplate('craft-single-sign-on/events/customprovider', [
            'customprovider' => $settings,
        ]);
    }

    public function actionOauthsettings(): Response
    {   
        $settings = (ResourcesController::actionDatadb('oauthsettings') != null)?ResourcesController::actionDatadb('oauthsettings'):Craftsinglesignon::$plugin->getSettings();
        
        if(Craft::$app->request->getQueryParam('events') == "delete"){

            ResourcesController::actionDatadelete('oauthsettings');
            return $this->renderTemplate('craft-single-sign-on/events/oauthsettings', [
                'oauthsettings' => Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('craft-single-sign-on'))->send(),
            ]);
        }

        return $this->renderTemplate('craft-single-sign-on/events/oauthsettings', [
            'oauthsettings' => $settings,
        ]);
    }

    public function actionOauthattribute(): Response
    {
        $attribute = (ResourcesController::actionDatadb('oauthattribute') != null)?ResourcesController::actionDatadb('oauthattribute'):Craftsinglesignon::$plugin->getSettings();
        
        return $this->renderTemplate('craft-single-sign-on/events/oauthattribute', array(
            'oauthattribute' => $attribute,
        ));
    }

    public function actionSamlsettings(): Response
    {   
        $settings = (ResourcesController::actionDatadb('samlsettings') != null)?ResourcesController::actionDatadb('samlsettings'):Craftsinglesignon::$plugin->getSettings();

        if(Craft::$app->request->getQueryParam('events') == "delete"){

            ResourcesController::actionDatadelete('samlsettings');
            return $this->renderTemplate('craft-single-sign-on/events/samlsettings', [
                'samlsettings' => Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('craft-single-sign-on'))->send(),
            ]);
        }

        return $this->renderTemplate('craft-single-sign-on/events/samlsettings', [
            'samlsettings' => $settings,
        ]);
    }

    public function actionSamlattribute(): Response
    {
        $attribute = (ResourcesController::actionDatadb('samlattribute') != null)?ResourcesController::actionDatadb('samlattribute'):Craftsinglesignon::$plugin->getSettings();
        
        return $this->renderTemplate('craft-single-sign-on/events/samlattribute', array(
            'samlattribute' => $attribute,
        ));
    }

    public function actionSamlprovider(): Response
    {
        $attribute = (ResourcesController::actionDatadb('samlprovider') != null)?ResourcesController::actionDatadb('samlprovider'):Craftsinglesignon::$plugin->getSettings();
        $site_url = (Craft::$app->version>4)?getenv('PRIMARY_SITE_URL'):getenv('PRIMARY_SITE_URL');
        $attribute->provider_meta_data = '<?xml version="1.0"?><md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" entityID="'.$site_url.'"><md:SPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" AuthnRequestsSigned="true" WantAssertionsSigned="true"><md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="'.$site_url.'/'.$attribute->provider_logout_url.'"/><md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="'.$site_url.'/'.$attribute->provider_logout_url.'"/><md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="'.$site_url.'/'.$attribute->provider_login_url.'" index="1"/></md:SPSSODescriptor></md:EntityDescriptor>';
   
        return $this->renderTemplate('craft-single-sign-on/events/samlprovider', array(
            'samlprovider' => $attribute,
        ));
    }

    public function actionCustomsettings(): Response
    {
        $attribute = (ResourcesController::actionDatadb('customsettings') != null)?ResourcesController::actionDatadb('customsettings'):Craftsinglesignon::$plugin->getSettings();
        
        return $this->renderTemplate('craft-single-sign-on/events/customsettings', array(
            'customsettings' => $attribute,
        ));
    }

    public function actionSave(): ?Response
    {
        $this->requirePermission('edit-events');
        $details = array();
        $setting = Craftsinglesignon::$plugin->getSettings();
        $site_name = Craft::$app->sites->currentSite->name;

        $settings = (ResourcesController::actionDatadb() != null)?ResourcesController::actionDatadb():array();

        // oauth settings
        if($this->request->getBodyParam('pluginClass')=='oauth-attribute')
        {
            $details['email_attribute'] = $this->request->getBodyParam('email_attribute');
            $details['username_attribute'] = $this->request->getBodyParam('username_attribute');
            $details['firstname_attribute'] = $this->request->getBodyParam('firstname_attribute');
            $details['lastname_attribute'] = $this->request->getBodyParam('lastname_attribute');
            $settings['oauthattribute'] = $details;
        }
        if($this->request->getBodyParam('pluginClass')=='oauth-settings')
        {
            $details['app_provider'] = $this->request->getBodyParam('app_provider');
            $details['client_id'] = $this->request->getBodyParam('client_id');
            $details['client_secret'] = $this->request->getBodyParam('client_secret');
            $details['scope'] = $this->request->getBodyParam('scope');
            $details['authorization_url'] = $this->request->getBodyParam('authorization_url');
            $details['oauth_token_api'] = $this->request->getBodyParam('oauth_token_api');
            $details['user_info_api'] = $this->request->getBodyParam('user_info_api');
            $details['callback_url'] = $this->request->getBodyParam('callback_url');
            $details['noreg'] = "10";
            $details['update_date'] = $this->request->getBodyParam('update_date');
            $settings['oauthsettings'] = $details;
        }

        // saml settings
        if($this->request->getBodyParam('pluginClass')=='saml-attribute')
        {
            $details['email_attribute'] = $this->request->getBodyParam('email_attribute');
            $details['username_attribute'] = $this->request->getBodyParam('username_attribute');
            $details['firstname_attribute'] = $this->request->getBodyParam('firstname_attribute');
            $details['lastname_attribute'] = $this->request->getBodyParam('lastname_attribute');
            $settings['samlattribute'] = $details;
        }
        if($this->request->getBodyParam('pluginClass')=='saml-settings')
        {
            $details['meta_data'] = Utilities::sanitize_certificate( $this->request->getBodyParam('meta_data') );
            $details['app_provider'] = $this->request->getBodyParam('app_provider');
            $details['assertion_url'] = $this->request->getBodyParam('assertion_url');
            $details['issuer'] = $this->request->getBodyParam('issuer');
            $details['logout_url'] = $this->request->getBodyParam('logout_url');
            $details['login_url'] = $this->request->getBodyParam('login_url');
            $details['noreg'] = "10";
            $details['update_date'] = $this->request->getBodyParam('update_date');
            $settings['samlsettings'] = $details;
        }

        //custom settings
        if($this->request->getBodyParam('pluginClass')=='custom-settings')
        {
            $details['redirect_url'] = $this->request->getBodyParam('redirect_url');
            $details['grouphandle'] = $this->request->getBodyParam('grouphandle');
            $details['userRole'] = $this->request->getBodyParam('userRole');
            $settings['customsettings'] = $details;
        }
        
        // Insert query
        $prefix = (Craft::$app->version>4)?getenv('CRAFT_DB_TABLE_PREFIX'):getenv('DB_TABLE_PREFIX');
        Craft::$app->db->createCommand()
        ->upsert($prefix.'mologin_config', array(
            'id' => 1,
            'name' => $site_name,
            'options' => json_encode($settings),
            'siteId'  => 1,
        ))
        ->execute();

        $this->setSuccessFlash(Craft::t('craft-single-sign-on', 'Settings saved.'));
        $this->redirectToPostedUrl($setting);
        return $this->asJson(['success' => true]);
    }

}

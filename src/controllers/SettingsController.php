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
    protected $allowAnonymous = ['index', 'check', 'delete', 'save', 'deactivation', 'message', 'oauthsettings', 'oauthattribute', 'samlprovider', 'samlsettings', 'samlattribute', 'customsettings'];

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
        $site_url = Craft::$app->sites->primarySite->baseUrl;
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

    public function actionDeactivation(): ?string
    {

            echo $var = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <style>
                body {font-family: Arial, Helvetica, sans-serif;}
                .modal {
                    position: fixed; /* Stay in place */
                    z-index: 1; /* Sit on top */
                    padding-top: 100px; /* Location of the box */
                    left: 0;
                    top: 0;
                    width: 100%; /* Full width */
                    height: 100%; /* Full height */
                    overflow: auto; /* Enable scroll if needed */
                    background-color: rgb(0,0,0); /* Fallback color */
                    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
                }
                
                /* Modal Content */
                .modal-content {
                    position: relative;
                    background-color: #fefefe;
                    margin: auto;
                    padding: 40px;
                    border: 1px solid #888;
                    width: 30%;
                    min-height: 48%;
                    border-radius: 10px;
                    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
                    -webkit-animation-name: animatetop;
                    -webkit-animation-duration: 0.4s;
                    animation-name: animatetop;
                    animation-duration: 0.4s
                }
                
                .modal-header .modal-footer .modal-body {
                    padding: 2px 16px;
                } 

                .save {
                    position: absolute;
                    bottom: 2em;
                    right: 3em;
                    background-color: #4CAF50; /* Green */
                    border: none;
                    border-radius: 10px;
                    color: white;
                    padding: 20px;
                    text-decoration: none;
                    font-size: 16px;
                    margin: 4px 2px;
                    cursor: pointer;
                }

            </style>

            <div class="modal">
                <div class="modal-content">
                <form action="" method="get" id="feedbackform">
                    <div class="modal-header">
                        <h2>miniOrange Single Sign On Feedback</h2>
                    </div>
                    <div class="modal-body">
                        <input type="checkbox" id="nowork" name="query" value="Not working">
                        <label for="nowork"> Not Working</label><br><br>
                        <input type="checkbox" id="noidp" name="query" value="Looking for different IDP">
                        <label for="noidp"> Looking for Different IDP</label><br><br>
                        <input type="checkbox" id="noconfig" name="query" value="Difficulty in Configuring SSO">
                        <label for="noconfig"> difficulty in configuring SSO</label><br>
                        <h4>Tell us how we can improve</h4>
                        <textarea id="message" name="message" rows="6" cols="70"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button onclick="myFunction()" class="save">Submit</button>
                        <button onclick="document.location.reload(true)" class="save" style="right: 9em; background-color: #f44336;">Skip & Continue</button>
                    </div>
                </form>
                </div>
            </div>
            <script>
                function myFunction() {
                    var form = $("#feedbackform");
                    return $.ajax({
                        url: "'.Craft::$app->sites->primarySite->baseUrl.'/mosinglesignon/message",
                        data: form.serialize(),
                        success: function(data) {
                            window.location.href = window.location.href
                        }
                    });
                }
            </script>'; 

            $cookie_name = "mo_feedback";
            $cookie_value = "submitted";
            setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");

            if(!isset($_COOKIE[$cookie_name])) {
                exit;
            } else {
                return 1;
            }
            
    }

    public function actionMessage() 
    {
        $plugin = Craft::$app->plugins->getPlugin('craft-single-sign-on', false);
        $user_info = User::find()->admin()->one();
        $query_sub = @$_REQUEST['query'] ?: "Not Selected";
        $feedback = @$_REQUEST['message'] ?: "No Feedback";

        $ch = curl_init('https://login.xecurify.com/moas/rest/mobile/get-timestamp');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array());
        $currentTimeInMillis = curl_exec($ch);
        curl_close($ch);

        $customerKey = "16555";
        $apiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
        $query = " Craft SSO Plugin Installation : $user_info->email";
        $content = '<div> Hello, <br><br>First Name :
								<br><br>Company : '.$_SERVER['HTTP_HOST'].'
								<br><br>Email : <a href="mailto:'.$user_info->email.'" target="_blank">'.$user_info->email.'</a>
                                <br><br>Version : '.$plugin->version.'
                                <br><br>Query : '.$query_sub.'
                                <br><br>Feedback : '.$feedback.'</div>';
                                
        $fields = array(
            'customerKey'	=> $customerKey,
            'sendEmail' 	=> true,
            'email' 		=> array(
                'customerKey' 	=> $customerKey,
                'fromEmail' 	=> $user_info->email,
                'bccEmail' 		=> 'shopifysupport@xecurify.com ',
                'fromName' 		=> 'miniOrange',
                'toEmail' 		=> 'shopifysupport@xecurify.com ',
                'toName' 		=> 'shopifysupport@xecurify.com ',
                'subject' 		=> $query,
                'content' 		=> $content
            ),
        );
        
        $stringToHash = $customerKey .  $currentTimeInMillis . $apiKey;
        $hashValue = hash("sha512", $stringToHash);
        $field_string = json_encode($fields);
        
        $ch = curl_init('https://login.xecurify.com/moas/api/notify/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Customer-Key: ".$customerKey,
            "Timestamp: ".$currentTimeInMillis,
            "Authorization: ".$hashValue
            )
        ); 
        curl_exec($ch);
        curl_close($ch);
        return 1;
    }

}

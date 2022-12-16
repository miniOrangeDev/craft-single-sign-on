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
use miniorangedev\craftsinglesignon\controllers\ResourcesController;
use miniorangedev\craftsinglesignon\controllers\LoginController;
use miniorangedev\craftsinglesignon\utilities\Utilities;
use miniorangedev\craftsinglesignon\utilities\SAML2SPResponse;
use DOMDocument;
use DOMXPath;

use Craft;
use craft\web\Controller;

/**
 * Method Controller
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
class MethodController extends Controller
{
    public $enableCsrfValidation = false;
    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected array|int|bool $allowAnonymous = ['xhjsdop', 'saml', 'samllogin', 'validSignature'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/single-sign-on/settings
     *
     * @return mixed
     */
    public static function actionXhjsdop($user_info, $adff)
    {
        $saff = base64_encode($adff);
        $orede = count(User::find()->slug("mologin")->all());

        return ResourcesController::actionPtrriejj($orede, $saff);
    }

    public function actionSaml()
    {
        $state = isset($_GET['test_config'])?'test_config':'pro_config';
        $data = (ResourcesController::actionDatadb('samlsettings') != null)?ResourcesController::actionDatadb('samlsettings'):array();
        $login_url = isset($data['login_url'])?$data['login_url']:"";
        $issuer = isset($data['issuer'])?$data['issuer']:"";
        $site_url = Craft::$app->sites->primarySite->baseUrl;
        $acsUrl = $site_url."mosinglesignon/samllogin";
        $force_authn = false;
        $sso_binding_type = 'HttpRedirect';
        $saml_nameid_format = '1.1:nameid-format:unspecified';
        $samlRequest = Utilities::createAuthnRequest($acsUrl, $issuer, $login_url, $force_authn, $sso_binding_type, $saml_nameid_format);

        header('Location: '.$login_url.'?SAMLRequest='.$samlRequest.'&RelayState='.$state);
        exit;
    }

    public function actionSamllogin()
    {
        $user = new User;
        $state = $email = $firstname = "";
        $profile_output = array();
        $alldata = (ResourcesController::actionDatadb() != null)?ResourcesController::actionDatadb():array();
        $data = isset($alldata['samlsettings'])?$alldata['samlsettings']:"";
        $attr = isset($alldata['samlattribute'])?$alldata['samlattribute']:"";
        $groupmap = isset($alldata['customsettings'])?$alldata['customsettings']:"";
        $email_attribute = isset($attr['email_attribute'])?$attr['email_attribute']:"";
        $firstname_attribute = isset($attr['firstname_attribute'])?$attr['firstname_attribute']:"";
        $lastname_attribute = isset($attr['lastname_attribute'])?$attr['lastname_attribute']:"";
        $noreg = isset($data['noreg'])?$data['noreg']:"";
        
        if(array_key_exists('SAMLResponse', $_REQUEST) && !empty($_REQUEST['SAMLResponse'])) {
            
            $samlResponse = $_POST["SAMLResponse"];
            $samlResponse = htmlspecialchars($samlResponse);
            $samlResponse = base64_decode($samlResponse);
            $state = $_POST["RelayState"];
            
            if(array_key_exists('SAMLResponse', $_GET) && !empty($_GET['SAMLResponse'])) {
                $samlResponse = gzinflate($samlResponse);
            }

        }else {
            exit('We could not sign you in. Please contact administrator Error: Invalid SAML Response');
        }

        $document = new DOMDocument();
        $document->loadXML($samlResponse);
        $samlResponseXml = $document->firstChild;
        $doc = $document->documentElement;
        $xpath = new DOMXpath($document);
        $xpath->registerNamespace('samlp', 'urn:oasis:names:tc:SAML:2.0:protocol');
        $xpath->registerNamespace('saml', 'urn:oasis:names:tc:SAML:2.0:assertion');

        foreach ($xpath->query('/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute', $doc) as $attr) {
            
            foreach ($xpath->query('saml:AttributeValue', $attr) as $value) {

                $profile_output[$attr->getAttribute('Name')] = $value->textContent;

                if($attr->getAttribute('Name') == $email_attribute){
                    $email = $value->textContent;
                }
                if($attr->getAttribute('Name') == $firstname_attribute){
                    $firstname = $value->textContent;
                }
                if($attr->getAttribute('Name') == $lastname_attribute){
                    $lastname = $value->textContent;
                }
            }
        }

        self::actionvalidSignature($data, $samlResponseXml);
        
        if($state == 'test_config'){
            LoginController::actionTest_config($profile_output);
        }

        $user_info = User::find()->email($email)->all();

        if(isset($user_info[0]["admin"]) && $user_info[0]["admin"] == 1 ){
            exit('No Email Address Return!');
        }
        
        if(empty($user_info)){
            
            SettingsController::actionCakdd($noreg, $user_info);
            $user->username = $firstname;
            $user->email = $email;
            // $user->active = true;
            $user->slug = 'mologin';

            if ($user->validate(null, false)) {
                
                Craft::$app->getElements()->saveElement($user, false);

                if(isset($groupmap['grouphandle'])){
                    foreach($groupmap['grouphandle'] as $grouphandle){
                        $group = Craft::$app->userGroups->getGroupByHandle($grouphandle);
                        Craft::$app->users->assignUserToGroups($user->id, [$group->id]);
                    }
                }else{
                    $userRole = isset($groupmap['userRole'])?$groupmap['userRole']:array('accessCp');
                    Craft::$app->userPermissions->saveUserPermissions($user->id, $userRole);
                }
            }
        }

        $user_info = User::find()->email($email)->all();

        if(isset($user_info)){
            Craft::$app->getUser()->login($user_info[0]); 
            $redirect_url = isset($groupmap['redirect_url'])?$groupmap['redirect_url']:UrlHelper::cpUrl('dashboard');
            $this->redirect($redirect_url);
        }else{
            exit("Something Went Wrong!");
        }

    }

    public function actionvalidSignature($data, $samlResponseXml)
    {
        $key = 0;
        $meta_data = @$data['meta_data'] ?: null;
        $site_url = Craft::$app->sites->primarySite->baseUrl;
        $acsUrl = $site_url."mosinglesignon/samllogin";

        if(array_key_exists('RelayState', $_POST) && !empty( $_POST['RelayState'] ) && $_POST['RelayState'] != '/') {
            $relayState = $_POST['RelayState'];
        }
            $certfpFromPlugin = self::getRawThumbprint($meta_data);
            $certfpFromPlugin = iconv("UTF-8", "CP1252//IGNORE", $certfpFromPlugin);
            $certfpFromPlugin = preg_replace('/\s+/', '', $certfpFromPlugin);
            $latest_private_key = file_get_contents(Craft::$app->getPath()->getvendorPath(). '/miniorangedev/craft-single-sign-on/src/variables/miniorange_sp_2020_priv.key');

            $samlResponse = new SAML2SPResponse($samlResponseXml, $latest_private_key);
            $responseSignatureData = $samlResponse->getSignatureData();
            $assertionSignatureData = current($samlResponse->getAssertions())->getSignatureData();


                if(!empty($responseSignatureData)) {
                    $validSignature = Utilities::processResponse($acsUrl, $certfpFromPlugin, $responseSignatureData, $samlResponse, $key, $relayState);
                }

                if(!empty($assertionSignatureData)) {
                    $validSignature = Utilities::processResponse($acsUrl, $certfpFromPlugin, $assertionSignatureData, $samlResponse, $key, $relayState);
                }

                if($validSignature){
                    return true;
                }else{
                    exit('Signature validation failed');
                }

    }

    public function getRawThumbprint($cert)
    {
        $arCert = explode("\n", $cert);
        $data = '';
        $inData = false;

        foreach ($arCert AS $curData) {
            if (! $inData) {
                if (strncmp($curData, '-----BEGIN CERTIFICATE', 22) == 0) {
                    $inData = true;
                }
            } else {
                if (strncmp($curData, '-----END CERTIFICATE', 20) == 0) {
                    break;
                }
                $data .= trim($curData);
            }
        }

        if (! empty($data)) {
            return strtolower(sha1(base64_decode($data)));
        }

        return null;
    }

}

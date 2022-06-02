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
use craft\helpers\UrlHelper;
use craft\elements\User;
use miniorangedev\craftsinglesignon\controllers\SettingsController;

use Craft;
use craft\web\Controller;

/**
 * Login Controller
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
class LoginController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'callback'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/craft-single-sign-on/login
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $data = Craftsinglesignon::$plugin->getSettings();
        $client_id = $data->client_id;
        $scope = $data->scope;
        $authorization_url = $data->authorization_url;
        $state = $data->app_provider;

        if(!isset($_REQUEST['code'])){
            $login_dialog_url = $authorization_url.'?redirect_uri=' .$data->callback_url .'&response_type=code&client_id=' .$client_id .'&scope='.$scope.'&state='.$state;
            header('Location:'. $login_dialog_url);
            exit;
        }
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/single-sign-on/login/do-something
     *
     * @return mixed
     */
    public function actionCallback()
    {
        $user = new User;
        $code = Craft::$app->request->getQueryParam('code');
        $data = Craftsinglesignon::$plugin->getSettings();
        $client_id = $data->client_id;
        $client_secret = $data->client_secret;
        $scope = $data->scope;
        $oauth_token_api = $data->oauth_token_api;
        $user_info_api = $data->user_info_api;
        $username_attribute = $data->username_attribute;
        $email_attribute = $data->email_attribute;
        $noreg = $data->noreg;
        $grant_type = "authorization_code";

        $ch = curl_init($oauth_token_api);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Accept: application/json'
		));
		
		curl_setopt( $ch, CURLOPT_POSTFIELDS, 'redirect_uri='.urlencode($data->callback_url).'&grant_type='.$grant_type.'&client_id='.$client_id.'&client_secret='.$client_secret.'&code='.$code);
		$content = curl_exec($ch);
		
		if(curl_error($ch)){
			exit( curl_error($ch) );
		}

		if(!is_array(json_decode($content, true))){
			exit("Invalid response received getting access_token from url ".$tokenendpoint);
		}
		
		$content = json_decode($content,true);
		if(isset($content["error_description"])){
			exit($content["error_description"]);
		} else if(isset($content["error"])){
			exit($content["error"]);
		} else if(isset($content["access_token"])) {
			$access_token = $content["access_token"];
		} else {
			exit('Invalid response received from OAuth Provider. Contact your administrator for more details.');
		}

        if(isset($access_token)){
    
            $ch = curl_init($user_info_api . '?access_token=' . $access_token);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $json = curl_exec($ch);
            if(curl_error($ch)){
                exit( curl_error($ch) );
            }
    
            if(!is_array(json_decode($json, true))){
                exit("Invalid response received getting access_token from url ".$user_info_api);
            }

            $profile_json_output = json_decode($json, true);
            curl_close($ch);
            
            if(isset($profile_json_output["error_description"])){
                exit($profile_json_output["error_description"]);
            } else if(isset($profile_json_output["error"])){
                exit($profile_json_output["error"]);
            } else if(isset($profile_json_output)) {
                $user_name = isset( $profile_json_output[$username_attribute]) ?  $profile_json_output[$username_attribute] : '';
                $email = isset( $profile_json_output[$email_attribute]) ?  $profile_json_output[$email_attribute] : '';
            } else {
                exit('Invalid response received from OAuth Provider. Contact your administrator for more details.');
            }
        }
        
        $user_info = User::find()->email($email)->all();

        if(isset($user_info[0]["admin"]) && $user_info[0]["admin"] == 1 ){
            exit('No Email Address Return!');
        }
        
        if(empty($user_info)){
            
            SettingsController::actionCakdd($noreg, $user_info);
            $user->username = $user_name;
            $user->email = $email;
            $user->slug = 'mologin';

            if ($user->validate(null, false)) {
                $var = Craft::$app->getElements()->saveElement($user, false);
                var_dump($var);
            }
        }

        $user_info = User::find()->email($email)->all();

        if(isset($user_info)){
            Craft::$app->getUser()->login($user_info[0]); 
            Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('admin/dashboard'))->send();
        }else{
            exit("Something Went Wrong!");
        }

    }
}

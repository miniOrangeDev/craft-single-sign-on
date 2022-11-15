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
use miniorangedev\craftsinglesignon\controllers\ResourcesController;

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
    protected $allowAnonymous = ['index', 'callback', 'test_config', 'saml', 'samllogin', 'json_to_htmltable'];

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
        $alldata = ResourcesController::actionDatadb();
        $data = @$alldata['oauthsettings'] ?: null;
        $client_id = @$data['client_id'] ?: null;
        $scope = @$data['scope'] ?: null;
        $authorization_url = @$data['authorization_url'] ?: null;
        $state = @$data['app_provider'] ?: null;
        $callback_url = @$data['callback_url'] ?: null;

        if(isset($_GET['test_config'])){
            $alldata['test_config'] = 1;
            $site_name = Craft::$app->sites->currentSite->name;
            $prefix = (Craft::$app->version>4)?getenv('CRAFT_DB_TABLE_PREFIX'):getenv('DB_TABLE_PREFIX');
            Craft::$app->db->createCommand()->update($prefix.'mologin_config', ['options' => json_encode($alldata)], ['name' => $site_name])->execute();
        }
        
        if(!isset($_REQUEST['code'])){
            $login_dialog_url = $authorization_url.'?redirect_uri=' .$callback_url .'&response_type=code&client_id=' .$client_id .'&scope='.$scope.'&state='.$state;
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
        $alldata = (ResourcesController::actionDatadb() != null)?ResourcesController::actionDatadb():array();
        $data = @$alldata['oauthsettings'] ?: null;
        $attr = @$alldata['oauthattribute'] ?: null;
        $client_id = @$data['client_id'] ?: null;
        $client_secret = @$data['client_secret'] ?: null;
        $oauth_token_api = @$data['oauth_token_api'] ?: null;
        $user_info_api = @$data['user_info_api'] ?: null;
        $username_attribute = @$attr['username_attribute'] ?: null;
        $email_attribute = @$attr['email_attribute'] ?: null;
        $callback_url = @$data['callback_url'] ?: null;
        $grant_type = "authorization_code";
        $profile_json_output = array();

        $ch = curl_init($oauth_token_api);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt( $ch, CURLOPT_POSTFIELDS, 'redirect_uri='.urlencode($callback_url).'&grant_type='.$grant_type.'&client_id='.$client_id.'&client_secret='.$client_secret.'&code='.$code);
		$content = curl_exec($ch);
		
                if(curl_error($ch)){
		        exit( curl_error($ch) );
		}

        if(curl_error($ch)){
            exit(curl_error($ch));
        } else if(!is_array(json_decode($content, true))){
			exit("Invalid response received getting access_token from url ".$oauth_token_api);
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
                $user_name = @$profile_json_output[$username_attribute] ?: null;
                $email = @$profile_json_output[$email_attribute] ?: null;
            } else {
                exit('Invalid response received from OAuth Provider. Contact your administrator for more details.');
            }
        }
                
        if(isset($alldata['test_config'])){
            $alldata['test_config'] = null;
            $site_name = Craft::$app->sites->currentSite->name;
            $prefix = (Craft::$app->version>4)?getenv('CRAFT_DB_TABLE_PREFIX'):getenv('DB_TABLE_PREFIX');
            Craft::$app->db->createCommand()->update($prefix.'mologin_config', ['options' => json_encode($alldata)], ['name' => $site_name])->execute();
            self::actionTest_config($profile_json_output);
        }

        self::actionLogin_flow($alldata, $user_name, $email);

    }

    public static function actionLogin_flow($alldata, $user_name = null, $email = null){

        $user = new User;
        $user_info = User::find()->email($email)->all();
        $groupmap = @$alldata['customsettings'] ?: null;
        $noreg = @$alldata['oauthsettings']['noreg'] ?: null;
        
        if(isset($user_info[0]["admin"]) && $user_info[0]["admin"] == 1 ){
            exit('No Email Address Return!');
        }
        
        if(empty($user_info)){
            
            if(Craft::$app->getUser()->getIdentity())
                return;
            
            SettingsController::actionCakdd($noreg, $user_info);
            $user->username = $user_name;
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
                    $userRole = @$groupmap['userRole'] ?: array('accessCp');
                    Craft::$app->userPermissions->saveUserPermissions($user->id, $userRole);
                }
            }
        }

        $user_info = User::find()->email($email)->all();
        
        if(isset($user_info)){
            Craft::$app->getUser()->login($user_info[0]);
            $redirect_url = @$groupmap['redirect_url'] ?: UrlHelper::cpUrl('dashboard');
            Craft::$app->getResponse()->redirect($redirect_url);
        }else{ 
            exit("Error in login!");
        }

    }

    public static function actionTest_config($profile_json_output){

        $print = '<div style="color: #3c763d; background-color: #dff0d8; padding:2%; margin-bottom:20px; text-align:center; border:1px solid #AEDB9A; font-size:18pt;">TEST SUCCESSFUL</div>';
        $print .= self::actionJson_to_htmltable($profile_json_output);
        echo $print;
        exit();
    }
    
    public static function actionJson_to_htmltable($arr) {

        $str = "<table>";
        foreach ($arr as $key => $val) {
            $str .= "<tr>";
            $str .= "<td>$key</td>";
            $str .= "<td>";
            if (is_array($val)) {
                if (!empty($val)) {
                    $str .= self::actionJson_to_htmltable($val);
                }
            } else {
                $str .= "<strong>$val</strong>";
            }
            $str .= "</td></tr>";
        }
        $str .= "</table>
        <style>
                table, th, td {
                    border: 1px solid black;
                    border-collapse: collapse;
                    padding: 10px;
                    font-size: 15px;
                }
            </style>";

        return $str;
    }

}

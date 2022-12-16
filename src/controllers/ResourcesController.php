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

use Craft;
use craft\web\Controller;

/**
 * Resources Controller
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
class ResourcesController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected array|int|bool $allowAnonymous = ['ptrriejj', 'datadb'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/single-sign-on/settings
     *
     * @return mixed
     */
    public static function actionPtrriejj($orede, $saff)
    {
        if($orede <= base64_decode($saff)){
            return true;
        }else{
            exit("Your Registration Limit For 10 Users Has Been Extended");
        }
    }

    public static function actionDatadb($offset = null)
    {

        $site_name = Craft::$app->sites->currentSite->name;
        $db_select = (new \craft\db\Query()) 
        ->select(['options']) 
        ->from('{{%mologin_config}}')
        ->where(['name' => $site_name]) 
        ->one();

        $data = (isset($db_select['options']))?json_decode($db_select['options'],true):'';
        if(empty($offset)){
            return $data;
        }
        $settings = (isset($data[$offset]))?$data[$offset]:null;
        return $settings;
    }

    public static function actionDatadelete($offset = null)
    {

        $site_name = Craft::$app->sites->currentSite->name;
        $db_select = (new \craft\db\Query()) 
            ->select(['options']) 
            ->from('{{%mologin_config}}')
            ->where(['name' => $site_name]) 
            ->one();

        $alldata = (isset($db_select['options']))?json_decode($db_select['options'],true):'';
        $alldata[$offset] = array();
        Craft::$app->db->createCommand()->update('{{%mologin_config}}', ['options' => json_encode($alldata)], ['name' => $site_name])->execute();
        
        return 1;
    }
        
}

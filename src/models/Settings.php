<?php
/**
 * craft-single-sign-on plugin for Craft CMS 3.x
 *
 * Register
 *
 * @link      https://www.miniorange.com
 * @copyright Copyright (c) 2022 miniorangedev/craft-single-sign-on
 */

namespace miniorangedev\craftsinglesignon\models;

use miniorangedev\craftsinglesignon\Craftsinglesignon;

use Craft;
use craft\base\Model;

/**
 * Craftsinglesignon Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    miniorangedev/craft-single-sign-on
 * @package   Craftsinglesignon
 * @since     4.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some field model attribute
     *
     * @var string
     */
    public string $someAttribute = 'Some Default';
    public string $app_provider = 'App Name';
    public string $client_id = 'Client ID';
    public string $client_secret = 'Client Secret';
    public string $scope = 'Scope';
    public string $authorization_url = 'Authorization API';
    public string $oauth_token_api = 'Oauth token API';
    public string $user_info_api = 'User info API';
    public string $username_attribute = 'Username Attribute';
    public string $email_attribute = 'Email Attribute';
    public string $firstname_attribute = 'Firstname Attribute';
    public string $lastname_attribute = 'Lastname Attribute';
    public string $callback_url = 'mologin/callback';
    public string $noreg = '10';
    public string $update_date = '';

    public string $saml_provider = 'App Name';
    public string $assertion_url = 'ACS';
    public string $issuer = 'Entity ID';
    public string $logout_url = 'Logout Endpoint';
    public string $login_url = 'Logout Endpoint';
    public string $meta_data = 'x509';

    public string $provider_login_url = 'mosinglesignon/samllogin';
    public string $provider_logout_url = 'mosinglesignon/samllogout';
    public string $provider_meta_data = '';

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            ['someAttribute', 'string'],
            ['someAttribute', 'default', 'value' => 'Some Default'],
        ];
    }
}

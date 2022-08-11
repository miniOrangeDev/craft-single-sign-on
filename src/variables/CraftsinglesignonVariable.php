<?php
/**
 * craft-single-sign-on plugin for Craft CMS 3.x
 *
 * Craft Single Sign-On OAuth & OpenID Connect plugin allows unlimited sso / login ( Single Sign On ) with your Azure AD, Discord, G Suite / Google Apps or other custom OAuth 2.0, OpenID Connect providers.
 *
 * @link      https://github.com/miniorangedev
 * @copyright Copyright (c) 2022 miniorange
 */

namespace miniorangedev\craftsinglesignon\variables;

use miniorangedev\craftsinglesignon\Craftsinglesignon;
use craft\helpers\UrlHelper;

use Craft;

/**
 * craft-single-sign-on Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.craftsinglesignon }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    miniorange
 * @package   Craftsinglesignon
 * @since     1.0.0
 */
class CraftsinglesignonVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.craftsinglesignon.exampleVariable }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.craftsinglesignon.exampleVariable(twigValue) }}
     *
     * @param null $optional
     * @return string
     */
    public function loginUrl()
    {
        return UrlHelper::siteUrl()."mologin/login";
    }

    public function samlUrl()
    {
        return UrlHelper::siteUrl()."mosinglesignon/issuer";
    }
}

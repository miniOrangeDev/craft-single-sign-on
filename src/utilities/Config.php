<?php
/**
 * craft-single-sign-on plugin for Craft CMS 3.x
 *
 * Craft Single Sign-On OAuth & OpenID Connect plugin allows unlimited sso / login ( Single Sign On ) with your Azure AD, Discord, G Suite / Google Apps or other custom OAuth 2.0, OpenID Connect providers.
 *
 * @link      https://github.com/miniorangedev
 * @copyright Copyright (c) 2022 miniorange
 */

namespace miniorangedev\craftsinglesignon\utilities;

use miniorangedev\craftsinglesignon\Craftsinglesignon;
use miniorangedev\craftsinglesignon\assetbundles\configutility\ConfigUtilityAsset;

use Craft;
use craft\base\Utility;

/**
 * craft-single-sign-on Utility
 *
 * Utility is the base class for classes representing Control Panel utilities.
 *
 * https://craftcms.com/docs/plugins/utilities
 *
 * @author    miniorange
 * @package   Craftsinglesignon
 * @since     1.0.0
 */
class Config extends Utility
{
    // Static
    // =========================================================================

    /**
     * Returns the display name of this utility.
     *
     * @return string The display name of this utility.
     */
    public static function displayName(): string
    {
        return Craft::t('craft-single-sign-on', 'Config');
    }

    /**
     * Returns the utility’s unique identifier.
     *
     * The ID should be in `kebab-case`, as it will be visible in the URL (`admin/utilities/the-handle`).
     *
     * @return string
     */
    public static function id(): string
    {
        return 'craftsinglesignon-config';
    }

    /**
     * Returns the path to the utility's SVG icon.
     *
     * @return string|null The path to the utility SVG icon
     */
    public static function iconPath(): ?string
    {
        return Craft::getAlias("@miniorangedev/craftsinglesignon/assetbundles/configutility/dist/img/Config-icon.svg");
    }

    /**
     * Returns the number that should be shown in the utility’s nav item badge.
     *
     * If `0` is returned, no badge will be shown
     *
     * @return int
     */
    public static function badgeCount(): int
    {
        return 0;
    }

    /**
     * Returns the utility's content HTML.
     *
     * @return string
     */
    public static function contentHtml(): string
    {
        Craft::$app->getView()->registerAssetBundle(ConfigUtilityAsset::class);

        $someVar = 'Have a nice day!';
        return Craft::$app->getView()->renderTemplate(
            'craft-single-sign-on/_components/utilities/Config_content',
            [
                'someVar' => $someVar
            ]
        );
    }
}

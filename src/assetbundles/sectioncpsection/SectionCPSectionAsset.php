<?php
/**
 * craft-single-sign-on plugin for Craft CMS 3.x
 *
 * Register
 *
 * @link      https://www.miniorange.com
 * @copyright Copyright (c) 2022 miniorangedev/craft-single-sign-on
 */

namespace miniorangedev\craftsinglesignon\assetbundles\sectioncpsection;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * SectionCPSectionAsset AssetBundle
 *
 * AssetBundle represents a collection of asset files, such as CSS, JS, images.
 *
 * Each asset bundle has a unique name that globally identifies it among all asset bundles used in an application.
 * The name is the [fully qualified class name](http://php.net/manual/en/language.namespaces.rules.php)
 * of the class representing it.
 *
 * An asset bundle can depend on other asset bundles. When registering an asset bundle
 * with a view, all its dependent asset bundles will be automatically registered.
 *
 * http://www.yiiframework.com/doc-2.0/guide-structure-assets.html
 *
 * @author    miniorangedev/craft-single-sign-on
 * @package   Craftsinglesignon
 * @since     4.0.0
 */
class SectionCPSectionAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = "@miniorangedev/craftsinglesignon/assetbundles/sectioncpsection/dist";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/Section.js',
        ];

        $this->css = [
            'css/Section.css',
        ];

        parent::init();
    }
}

# Single Sign-On plugin for Craft CMS 3.x or 4.x 

Single Sign-On

![Screenshot](resources/img/miniorange.png)

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require miniorangedev/craft-single-sign-on

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Single Sign-On.

## Single Sign-On Overview

Enable Seamless Single Sign On (SSO) Login for your Craft CMS based website using our plugin. Enable secure one-click access to the users stored in your existing Identity Provider (IDP). Configure SSO with integration protocols like SAML 2.0, OAuth 2.0 and JWT for different IDPs like Okta, ADFS, Azure AD, Azure B2C, AWS Cognito, GSuite/Google Apps including social media providers like Discord, Facebook, etc. Our plugin enables secure and easy login to your website using a single set of credentials.

## Configuring Single Sign-On

Just a couple of lines on your Login twig template and Users will be able to SSO on one click.
Copy and Paste the following code into the required .twig file.
```html
<a href="{{ craft.craftsinglesignon.loginUrl() }}">SSO with OAuth</a>
```

Use following code for SAML login.
```html
<a href="{{ craft.craftsinglesignon.samlUrl() }}">SSO with SAML</a>
```

## Using Single Sign-On

Our solution ensures easy, secured and seamless login to Craft using existing credentials which they use to access the Identity Provider/other connected applications
With our expert assistance, you can take your craft-based website to the next level and provide your customers with an enhanced Single Sign-On (SSO) experience.

## Features

<b>Custom Attribute Mapping :</b><br>
Plugin allows mapping any custom user attributes received from OAuth / OpenId / SAML Connect provider to any Craft user attribute.

<b>User Sync :</b><br>
New users can be auto-created during Single Sign-On while existing users can log in into their existing Craft user profile. Sync user profile attributes such as First Name, Last Name, Email Address etc. present in your IDP to Craft customer profile fields.

<b>Widget Button Customization :</b><br>
You can configure the login widget without any technical knowledge, you can select suitable style attributes from our widget style menu or contact us for customizing the widget for your custom requirements or to report any features missing in our app.
 
<b>Account Linking :</b><br>
After user SSO to Craft, if the user already exists in Craft, then his profile gets updated or it will create a new User
 
<b>Redirect URL after Login :</b><br>
Craft Single Sign On ( OAuth Login ) automatically redirects users after successful login.

## Supported Identity Providers :

<ul>
        <li>OKTA</li>
        <li>ADFS</li>
        <li>Azure B2C</li>
        <li>One Login</li>
        <li>Salesforce</li>
        <li>Azure AD</li>
        <li>Auth0</li>
        <li>Discord</li>
        <li>Google</li>
        <li>Office 365 </li>
        <li>AWS Cognito</li>
        <li>Clever</li>
        <li>Ping</li>
        <li>Keycloak </li>
        <li>LinkedIn</li>
        <li>Onelogin</li>
        <li>Salesforce</li>
        <li>Slack</li>
        <li>Amazon</li>
        <li>Twitter</li>
        <li>Apple</li>
        <li>G Suite & many more</li>
</ul>


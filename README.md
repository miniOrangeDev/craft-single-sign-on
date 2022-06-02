# single-sign-on plugin for Craft CMS 3.x

single sign on

![Screenshot](resources/img/miniorange.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require miniorange/single-sign-on

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for single-sign-on.

## single-sign-on Overview

Enable Seamless Single Sign On (SSO) Login for your Craft CMS based website using our plugin. Enable secure one-click access to the users stored in your existing Identity Provider (IDP). Configure SSO with integration protocols like SAML 2.0, OAuth 2.0 and JWT for different IDPs like Okta, ADFS, Azure AD, Azure B2C, AWS Cognito, GSuite/Google Apps including social media providers like Discord, Facebook, etc. Our plugin enables secure and easy login to your website using a single set of credentials.

## Configuring single-sign-on

Just a couple of lines on your Login twig template and Users will be able to SSO on one click.
Copy and Paste the following code into the required .twig file.
```html
<a href="{{ craft.singlesignon.loginUrl() }}">Login with miniorange</a>
```



## Using single-sign-on

Using for single sign on into your Craft CMS instance with social provider passwordless.

## Features

Single Sign-On

Enable a Seamless Single Sign-On (SSO) experience for your users so they can access your Craft CMS based website using their existing IDP credentials providing an affiliated login experience with secured one-click login access.

Attribute Mapping

Sync user profile attributes such as first name, last name, tags, address, etc. present in your Identity Provider (IDP) to your website.

Multiple IDPs Supported

Configure SSO support for multiple IDPs and authenticate different types of users with different IDPs. 

Complete website protection

Restrict your website only to the users present in your IDP and block or redirect all other users to a different website. Secure your website’s content.


## single-sign-on Roadmap

Configure Google, Azure AD, Okta etc providers:

* Release it

Brought to you by [miniorange](https://github.com/miniorange)

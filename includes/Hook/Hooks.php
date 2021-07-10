<?php

namespace DiscourseConnect\Hook;


class Hooks
{
    public static $password_providers = [
        'MediaWiki\Auth\LocalPasswordPrimaryAuthenticationProvider',
        'MediaWiki\Auth\TemporaryPasswordPrimaryAuthenticationProvider'
    ];
    public static function onRegistration()
    {
        // see https://github.com/wikimedia/mediawiki-extensions-PluggableAuth/blob/master/includes/PluggableAuthHooks.php
        // enable auto creation for extension
        $GLOBALS['wgGroupPermissions']['*']['autocreateaccount'] = true;
        // remove default auth provider by default
        if (!$GLOBALS['wgDiscourseConnectEnableLocalLogin']) {
            $providers = $GLOBALS['wgAuthManagerAutoConfig'];
            if (isset($providers['primaryauth'])) {
                $primaries = $providers['primaryauth'];
                foreach ($primaries as $key => $provider) {
                    if (in_array($provider['class'], self::$password_providers)) {
                        unset($GLOBALS['wgAuthManagerAutoConfig']['primaryauth'][$key]);
                    }
                }
            }
        }
    }

    public static function log()
    {
        if (!$GLOBALS['wgDebugToolbar']) {
            return;
        }
        \MWDebug::log(print_r($GLOBALS['DiscourseConnectDebugValues'], true));
    }
}

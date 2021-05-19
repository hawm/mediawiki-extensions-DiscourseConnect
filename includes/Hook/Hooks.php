<?php

namespace DiscourseConnect\Hook;

//https://github.com/wikimedia/mediawiki-extensions-PluggableAuth/blob/master/includes/PluggableAuthHooks.php

class Hooks {
    public static $password_providers = [
            'MediaWiki\Auth\LocalPasswordPrimaryAuthenticationProvider',
            'MediaWiki\Auth\TemporaryPasswordPrimaryAuthenticationProvider'
        ];
    public static function onRegistration(){
        // remove providers when disable local login 
        if($GLOBALS['wgDiscourseConnectEnableLocalLogin']){
            return;
        }
        $providers = $GLOBALS['wgAuthManagerAutoConfig'];
        if(isset($providers['primaryauth'])){
            $primaries = $providers['primaryauth'];
            foreach($primaries as $key => $provider){
                if(in_array($provider['class'], self::$password_providers)){
                    unset($GLOBALS['wgAuthManagerAutoConfig']['primaryauth'][$key]);
                }
            }
        }
    }

    public static function logTest(){
        if(!$GLOBALS['wgDebugToolbar']){
            return;
        }
        \MWDebug::log(print_r($GLOBALS['DiscourseConnectDebug'], true));
    }

}
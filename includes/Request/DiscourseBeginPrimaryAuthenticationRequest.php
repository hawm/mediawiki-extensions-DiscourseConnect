<?php

namespace DiscourseConnect\Request;

use MediaWiki\Auth\ButtonAuthenticationRequest;

class DiscourseBeginPrimaryAuthenticationRequest extends ButtonAuthenticationRequest{
    const BUTTON_NAME = 'discourseconnectlogin';

    public function __construct(){
        parent::__construct(
            self::BUTTON_NAME,
            wfMessage('discourseconnect-info-login-button-label'),
            wfMessage('discourseconnect-info-login-button-help'),
            true
        );
    }

    public function getFieldInfo(){
        return parent::getFieldInfo() + 
        /* 
        * addition hidden field for keep display the login button 
        * instead of redirect to Discourse directly.
        * see LoginSignupSpecialPage->canByPassForm
        */
        [
            '_keep' => [
                'type' => 'hidden',
                'optional' => true
            ]
        ];
    }

    public static function getRequest(array $reqs){
        return self::getRequestByName($reqs, self::BUTTON_NAME);
    }


}
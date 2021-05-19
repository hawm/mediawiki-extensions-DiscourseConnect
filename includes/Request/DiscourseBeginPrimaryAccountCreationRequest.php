<?php

namespace DiscourseConnect\Request;

use MediaWiki\Auth\AuthenticationRequest;

class DiscourseBeginPrimaryAccountCreationRequest extends AuthenticationRequest {
    public function __construct(){
        $this->description = wfMessage('discourseconnect-info-account-creation-description');
    }
    public function getFieldInfo(){
        return [
           'info' => [
               'type' => 'null',
               'value' => $this->description
            ]
           ];
    }

    public static function getRequest(array $reqs){
        return self::getRequestByClass($reqs, self::class);
    }
}
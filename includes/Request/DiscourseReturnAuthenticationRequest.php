<?php

namespace DiscourseConnect\Request;

use Mediawiki\Auth\AuthenticationRequest;

class DiscourseReturnAuthenticationRequest extends AuthenticationRequest{

    public static function getRequest(array $reqs){
        return self::getRequestByClass($reqs, self::class);
    }

    public function getFieldInfo(){
        return [
            'sso' => [
                'type'=> 'string'
            ],
            'sig' => [
                'type'=> 'string'
            ],
        ];
    }

}

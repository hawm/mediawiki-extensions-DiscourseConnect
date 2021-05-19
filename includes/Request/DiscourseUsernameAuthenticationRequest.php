<?php

namespace DiscourseConnect\Request;

use MediaWiki\Auth\UsernameAuthenticationRequest;

class DiscourseUsernameAuthenticationRequest extends UsernameAuthenticationRequest{
    public function __construct($username=null){
        $this->username = $username;
    }

    public function getFieldInfo(){
        $fields = parent::getFieldInfo();
        if ($this->username) {
            $fields['username']['value'] = $this->username;
        }
        return $fields;
    }

    public static function getRequest(array $reqs){
        return self::getRequestByClass($reqs, self::class);
    }
}
<?php

namespace DiscourseConnect\Request;

use DiscourseConnect\Request\DiscourseRequest;
use MediaWiki\Auth\AuthenticationRequest;

class DiscourseReturnRequest extends AuthenticationRequest
{
    use DiscourseRequest;

    public function getFieldInfo()
    {
        return [
            'sso' => [
                'type' => 'string'
            ],
            'sig' => [
                'type' => 'string'
            ]
        ];
    }
}

<?php

namespace DiscourseConnect\Request;

use DiscourseConnect\Request\DiscourseRequest;
use MediaWiki\Auth\AuthenticationRequest;

class DiscourseAccountCreationRequest extends AuthenticationRequest
{
    use DiscourseRequest;

    public function __construct()
    {
        $this->description = wfMessage('dc-info-account-creation-description');
    }
    public function getFieldInfo()
    {
        return [
            'info' => [
                'type' => 'null',
                'value' => $this->description
            ]
        ];
    }
}

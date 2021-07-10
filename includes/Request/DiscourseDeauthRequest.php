<?php

namespace DiscourseConnect\Request;

use DiscourseConnect\Request\DiscourseRequest;
use MediaWiki\Auth\ButtonAuthenticationRequest;
use Message;

class DiscourseDeauthRequest extends ButtonAuthenticationRequest
{
    use DiscourseRequest;

    public function __construct($buttonName)
    {
        parent::__construct(
            $buttonName,
            new Message("dc-info-$buttonName-label"),
            new Message("dc-info-$buttonName-help"),
            true
        );
    }
}

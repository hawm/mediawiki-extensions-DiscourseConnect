<?php

namespace DiscourseConnect;

use Mediawiki\MediawikiServices;
use DiscourseConnect\Service\DiscourseConnectConsumer;
use DiscourseConnect\Service\DiscourseUserService;

return [
    'DiscourseConnectConsumer' => 
    function (MediaWikiServices $services) : DiscourseConnectConsumer{
        return new DiscourseConnectConsumer(
            $services->getConfigFactory()->makeConfig('discourseconnect')
        );
    },
    'DiscourseUserService' =>
    function (MediawikiServices $services) : DiscourseUserService {
        return new DiscourseUserService(
            $services->getConfigFactory()->makeConfig('discourseconnect'),
            $services->getDBLoadBalancer(),
        );
    }
];
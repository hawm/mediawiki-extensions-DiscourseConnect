<?php

use Mediawiki\MediawikiServices;
use DiscourseConnect\Service\DiscourseUserService;
use DiscourseConnect\Service\DiscourseConnectConsumer;
use DiscourseConnect\Service\DiscourseGroupService;

return [
    'DiscourseUserService' => 
    function (MediaWikiServices $services) : DiscourseUserService {
        return new DiscourseUserService(
            $services->getConfigFactory()->makeConfig('discourseconnect'),
            $services->getDBLoadBalancer(), 
            $services->getUserFactory()
        );
    },
    'DiscourseConnectConsumer' => 
    function (MediaWikiServices $services) : DiscourseConnectConsumer{
        return new DiscourseConnectConsumer(
            $services->getConfigFactory()->makeConfig('discourseconnect'),
            $services->getAuthManager()
        );
    },
    'DiscourseGroupService' => 
    function (MediaWikiServices $services) : DiscurseGroupService{
        return new DiscourseGroupService(
            $services-getConfigFactory()->makeConfig('discourseconnect'),
            $services->getUserGroupManager()
        );
    }
    
];
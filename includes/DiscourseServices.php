<?

namespace DiscourseConnect;

use MediaWiki\MediaWikiServices;

class DiscourseServices {
    private static function getService(?MediaWikiServices $services, $name){
        if($services === null){
            $services = MediaWikiServices::getInstance();
        }
        return $services->getService($name);
    }

    public static function getDiscourseUserService(MediaWikiServices $services = null){
        return self::getService($services, 'DiscourseUserService');
    }

    public static function getDiscourseConnectConsumer(MediaWikiServices $services = null){
        return self::getService($services, 'DiscourseConnectConsumer');
    }

    public static function getDiscourseGroupService(MediaWikiServices $services = null){
        return self::getService($services, 'DiscourseGroupService');
    }
}
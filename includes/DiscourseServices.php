<?

namespace DiscourseConnect;

use DiscourseConnect\Service\DiscourseConnectConsumer;
use DiscourseConnect\Service\DiscourseUserService;
use MediaWiki\MediaWikiServices;

class DiscourseServices
{
    private static function getService(?MediaWikiServices $services, $name)
    {
        return ($services ?: MediaWikiServices::getInstance())->getService($name);
    }

    public static function getDiscourseConnectConsumer(
        MediaWikiServices $services = null
    ): DiscourseConnectConsumer {
        return self::getService($services, 'DiscourseConnectConsumer');
    }

    public static function getDiscourseUserService(
        MediaWikiServices $services = null
    ): DiscourseUserService {
        return self::getService($services, 'DiscourseUserService');
    }
}

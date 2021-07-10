<?php

namespace DiscourseConnect;

use Mediawiki\Preferences\Hook\GetPreferencesHook;

class CustomizeUserPreferences implements GetPreferencesHook {
    public function onGetPreferences($user, &$preferences)
    {
        $preferences['discoursexternalid'] = [
            'type' => 'text',
            'disabled' => true
        ];
    }
}
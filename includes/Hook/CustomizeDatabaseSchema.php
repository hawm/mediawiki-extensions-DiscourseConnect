<?php

namespace DiscourseConnect\Hook;

use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class CustomizeDatabaseSchema implements LoadExtensionSchemaUpdatesHook {
    public function onLoadExtensionSchemaUpdates($updater){
        $this->createDiscourseUserTable($updater);
    }

    public function createDiscourseUserTable($updater){
        $updater->addExtensionTable(
            'discourse_user',
            dirname(__FILE__, 3) . '/sql/CreateDiscourseUserTable.sql'
        );
    }
}
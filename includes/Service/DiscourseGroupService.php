<?php

namespace DiscourseConnect\Service;

use MediaWiki\User\UserIdentity;
use MediaWiki\User\UserGroupManager;

class DiscourseGroupService{
    public function __construct(
        \Config $config,
        UserGroupManager $userGroupManager
    ){
        $this->config = $config;
        $this->userGroupManager = $userGroupManager;
    }

    public function getGroupMapping(){
        // don't set default value as array in extenion
        // see DiscourseUserService->getUserMapping
        return $this->config->get('DiscourseConnectGroupMapping');
    }

    public function getNewGroups(array $groupMapping, array $discourseGroups){
        $newGroups = [];
        foreach($discourseGroups as $dg){
            $mwGroups = $groupMapping[$dg] ?? [];
            if(is_string($mwGroups)){
            }elseif(is_array($mwGroups)){
                foreach($mwGroups as $mwg){
                    if(!is_string($mwg)){
                        continue;
                    }
                    $newGroups[$mwg] = true;
                }
            }else{
                // do nothing
            }
        }
        return $newGroups;
    }

    public function populateGroups(
        UserIdentify $user,
        array $discourseGroups,
        ){
        $groupMapping = $this->getGroupMapping();
        if(!is_array($groupMapping)){
            wfDebug('discourseconnect', 'Config `DiscourseConnectGroupMapping` not array');
            return;
        }

        $newGroups = getNewGroups($groupMapping, $discourseGroups );
        $currentGroups = array_flip($this->userGroupManager->getUserGroups($user));

        $removedGroups = array_diff_keys($currentGroups, $newGroups);
        $addedGroups = array_diff_keys($newGroups, $currentGroups);

        $this->removeUserFromGroups($removedGroups);
        $this->addUserToGroups($addedGroups);

    }


    function addUserToGroups(UserIdentify $user, array $groups){
        foreach($groups as $g){
            $this->userGroupManager->addUserToGroup($user, $g);
        }
    }
    
    function removeUserFromGroups(UserIdentify $user, array $groups){
        foreach($groups as $g){
            $this->userGroupManager->removeUserFromGroup($user, $g);
        }
    }
}

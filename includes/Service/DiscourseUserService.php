<?php

namespace DiscourseConnect\Service;

use Wikimedia\Rdbms\ILoadBalancer;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserGroupManager;
use MediaWiki\User\UserIdentity;

class DiscourseUserService{
    public function __construct(
        \Config $config,
        ILoadBalancer $loadBalancer, 
        UserFactory $userFactory
        ){
        $this->loadBalancer = $loadBalancer;
        $this->userFactory = $userFactory;
        $this->config = $config;
    }

    public function getUserMapping(){
        /* 
        * Don't set default value as array in extension.json 
        * to avoid `id` array key be convert to numberic array key 
        * when call array_merge by MediaWiki core that to load config
        */
        return $this->config->get('DiscourseConnectUserMapping');
    }

    public function newUserFromExternalId($discourseExternalId){
        // load user mapping first
        $user_mapping = $this->getUserMapping();
        if (is_array($user_mapping) &&
            $mediawiki_user_name = $user_mapping[$discourseExternalId] ?? null){
            wfDebug('discourseconnect', "Try to login mapping user 
            external_id = $discourseExternalId, username = $mediawiki_user_name");
            return $this->userFactory->newFromName($mediawiki_user_name);
        }else{
            wfDebug('discourseconnect', 'Config `DiscourseConnectUserMapping` not array');
        }
        $dbr = $this->loadBalancer->getConnectionRef(DB_REPLICA);
        $mediawiki_user_id = $dbr->selectField(
            'discourse_user',
            'mediawiki_user_id',
            "discourse_external_id = $discourseExternalId",
        );
        if(!$mediawiki_user_id){
            wfDebug('discourseconnect', "No exist user with 
            discourse_external_id = $discourseExternalId");
            return null;
        }
        return $this->userFactory->newFromId($mediawiki_user_id);
    }

    public function linkUser(
        UserIdentity $user, 
        $DiscourseExternalId, 
        bool $update=false
        ){
        // TODO: support update linked user
        // TODO: support connection mode 
        $dbw = $this->loadBalancer->getConnectionRef(DB_MASTER);
        $ret = $dbw->insert(
            'discourse_user',
            [
                'mediawiki_user_id' => $user->getId(),
                'discourse_external_id' => $DiscourseExternalId
            ],
            __METHOD__
            );
        return $ret;
        // TODO: do something when $ret==false that link user failed
    }

    public function unlinkUser(UserIdentity $user){
        $dbw = $this->loadBalancer->getConnectionRef(DB_MASTER);
        $ret = $dbw->delete(
            'discourse_user',
            [
                'mediawiki_user_id' => $user->getId()
            ],
            __METHOD__
            );
        return $ret;
    }

    // TODO populate other user properties like email or realname
}
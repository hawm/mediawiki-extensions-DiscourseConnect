<?php

namespace DiscourseConnect\Service;

use Config;
use Wikimedia\Rdbms\ILoadBalancer;

class DiscourseUserService
{
    const TABLE_NAME = 'discourse_user';
    const TABLE = [
        'mid' => 'mediawiki_user_id',
        'eid' => 'discourse_external_id'
    ];

    protected $loadBalancer;
    protected $userFactory;
    protected $config;

    public function __construct(
        Config $config,
        ILoadBalancer $loadBalancer
    ) {
        $this->loadBalancer = $loadBalancer;
        $this->config = $config;
    }

    public function getUserIdByExternalId(int $eId): int
    {
        $dbr = $this->getConnection();
        $mId = $dbr->selectField(
            self::TABLE_NAME,
            self::TABLE['mid'],
            [
                self::TABLE['eid'] => $eId
            ]
        );
        return $mId;
    }


    public function getExternalIdByUserId(int $mId): int
    {
        $dbr = $this->getConnection();
        $eId = $dbr->selectField(
            self::TABLE_NAME,
            self::TABLE['eid'],
            [
                self::TABLE['mid'] => $mId
            ]
        );

        return $eId;
    }

    public function getExternalIdByUserName($username): int
    {
        $dbr = $this->getConnection();
        $mId = $dbr->selectField(
            'user',
            'user_id',
            [
                'user_name' => $username,
            ]
        );
        $eId = $this->getExternalIdByUserId($mId);
        return $eId;
    }

    public function linkUserById(int $mId, int $eId): bool
    {
        $dbw = $this->getConnection(true);
        $ret = $dbw->insert(
            self::TABLE_NAME,
            [
                self::TABLE['mid'] => $mId,
                self::TABLE['eid'] => $eId
            ],
        );
        return $ret;
    }

    public function unlinkUserByUserId(int $mId): bool
    {
        $dbw = $this->getConnection(true);
        $ret = $dbw->delete(
            self::TABLE_NAME,
            [
                self::TABLE['mid'] => $mId
            ],
        );
        return $ret;
    }

    public function unlinkUserByExternalId(int $eId): bool
    {
        $dbw = $this->getConnection(true);
        $ret = $dbw->delete(
            self::TABLE_NAME,
            [
                self::TABLE['eid'] => $eId
            ],
        );
        return $ret;
    }

    protected function getConnection($write = false)
    {
        return $this->loadBalancer->getConnectionRef($write ? DB_PRIMARY : DB_REPLICA);
    }


    // TODO populate other user properties like email or realname
}

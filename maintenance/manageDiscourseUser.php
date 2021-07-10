<?php
require_once getenv('MW_INSTALL_PATH') !== false
    ? getenv('MW_INSTALL_PATH') . '/maintenance/Maintenance.php'
    : __DIR__ . '/../../../maintenance/Maintenance.php';

use DiscourseConnect\DiscourseServices;
use MediaWiki\MediaWikiServices;

abstract class ManageDiscourseUser extends Maintenance
{
    static $mUserDescription = "MediaWiki username starts with @, or user id if starts with #";
    static $dUserDescription = "Discourse user id starts with #";


    public function __construct()
    {
        parent::__construct();
        $this->requireExtension('DiscourseConnect');
        $this->addDescription('Manage Discourse user');
    }

    public function isId($option)
    {
        return (bool)preg_match('/^#\d+$/', $option);
    }

    public function isName($option)
    {
        return (bool)preg_match('/^@.+$/', $option);
    }

    public function getIdOrName($option)
    {
        return substr($option, 1);
    }

    public function getMuser($option): User
    {
        if ($this->isId($option)) {
            $user = $this->getUserFactory()->newFromId($this->getIdOrName($option));
        } elseif ($this->isName($option)) {
            $user = $this->getUserFactory()->newFromName($this->getIdOrName($option));
        } else {
            $this->fatalError("ERROR: invalid value '$option'");
        }
        return $user;
    }

    public function getDuser($option)
    {
        if (!$this->isId($option)) {
            $this->fatalError("ERROR: invalid value '$option'");
        }
        return $this->getIdOrName($option);
    }
    
    public function getUserFactory(){
        return MediaWikiServices::getInstance()->getUserFactory();
    }

    public function getDiscourseUserService(){
        return DiscourseServices::getDiscourseUserService();
    }

}

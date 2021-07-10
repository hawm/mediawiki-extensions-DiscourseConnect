<?php
require_once 'manageDiscourseUser.php';

class CheckUser extends ManageDiscourseUser
{
    public function __construct()
    {
        parent::__construct();
        $this->addArg(
            'user',
            self::$mUserDescription,
            true
        );

        $this->addOption(
            'discourse',
            'Treat <user> argument as a Discourse user id, must starts with #',
            false,
            false,
            'd'
        );
    }

    public function execute()
    {
        if ($this->hasOption('discourse')) {
            $dUser = $this->getDuser($this->getArg(0));
            $mUser = $this->getMuser('#' . $this->getDiscourseUserService()->getUserIdByExternalId($dUser));
        } else {
            $mUser = $this->getMuser($this->getArg(0));
            $dUser = $this->getDiscourseUserService()->getExternalIdByUserId($mUser->getId());
        }
        $this->output("MediaWiki User: #{$mUser->getId()}, @{$mUser->getName()}\n");
        $this->output("Discourse User: #{$dUser}\n");
    }

}

$maintClass = CheckUser::class;

require_once RUN_MAINTENANCE_IF_MAIN;

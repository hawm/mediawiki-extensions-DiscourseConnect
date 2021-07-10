<?php

require_once 'manageDiscourseUser.php';

class UnlinkUser extends ManageDiscourseUser
{
    public function __construct()
    {
        parent::__construct();
        $this->addArg(
            'user',
            self::$mUserDescription,
        );
        $this->addOption(
            'discourse',
            'Treat <user> argument as Discourse user id, must starts with #',
            false,
            false,
            'd'
        );
    }

    public function execute()
    {
        if ($this->hasOption('discourse')) {
            $dUser = $this->getDuser($this->getArg(0));
            $ret = $this->getDiscourseUserService()->unlinkUserByExternalId($dUser);
        } else {
            $mUser = $this->getMuser($this->getArg(0));
            $ret = $this->getDiscourseUserService()->unlinkUserByUserId($mUser->getId());
        }
        if($ret){
            $output = "Unlink Succeed!";
        }else{
            $output = "Unlink Failed!";
        }
        $this->output($output."\n");
    }
}


$maintClass = UnlinkUser::class;

require_once RUN_MAINTENANCE_IF_MAIN;
<?php

require_once 'manageDiscourseUser.php';

class LinkUser extends ManageDiscourseUser
{
    public function __construct()
    {
        parent::__construct();
        $this->addArg(
            'muser',
            self::$mUserDescription,
            true
        );
        $this->addArg(
            'duser',
            self::$dUserDescription,
            true
        );
    }

    public function execute()
    {
        $mUser = $this->getMuser($this->getArg(0));
        $dUser = $this->getDuser($this->getArg(1));
        $ret = $this->getDiscourseUserService()->linkUserById($mUser->getId(), $dUser);
        if($ret){
            $output = "Link Succeed!";
        }else{
            $output = "Link Failed!";
        }

        $this->output($output."\n");
    }
}

$maintClass = LinkUser::class;

require_once RUN_MAINTENANCE_IF_MAIN;
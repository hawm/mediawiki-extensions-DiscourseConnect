<?php

namespace DiscourseConnect\Request;

use DiscourseConnect\Request\DiscourseRequest;
use MediaWiki\Auth\ButtonAuthenticationRequest;
use Message;

class DiscourseAuthRequest extends ButtonAuthenticationRequest
{
    use DiscourseRequest;

    public $pageName;
    public $tokenName;
    public $disabled;
    protected $canBypass;

    public function __construct(
        $pageName,
        $tokenName,
        $buttonName,
        $canBypass = false,
        $disabled = false
    ) {
        $this->pageName = $pageName;
        $this->tokenName = $tokenName;
        $this->canBypass = $canBypass;
        $this->disabled = $disabled;
        $this->init_button($buttonName);
    }

    public function init_button($name, $required = true)
    {
        parent::__construct(
            $name,
            new Message("dc-info-$name-label"),
            new Message("dc-info-$name-help"),
            $required
        );
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFieldInfo()
    {
        $fieldInfo = parent::getFieldInfo();
        // addition field to keep our button always be display
        // instead of redirect directly
        // see LoginSignupSpecialPage::canBypassForm()
        if (!$this->canBypass) {
            $fieldInfo = array_merge($fieldInfo, [
                '_keep' => [
                    'type' => 'hidden',
                    'optional' => true
                ]
            ]);
        }
        return $fieldInfo;
    }

}

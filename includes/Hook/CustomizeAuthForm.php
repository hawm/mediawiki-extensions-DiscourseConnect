<?php

namespace DiscourseConnect\Hook;

use MediaWiki\SpecialPage\Hook\AuthChangeFormFieldsHook;
use MediaWiki\Auth\AuthManager;
use DiscourseConnect\Request;

use function GuzzleHttp\default_ca_bundle;

class CustomizeAuthForm implements AuthChangeFormFieldsHook
{
    public function onAuthChangeFormFields(
        $reqs,
        $fieldInfo,
        &$formDescriptor,
        $action
    ) {
        switch ($action) {
            case AuthManager::ACTION_LOGIN:
                $this->sortLoginButton($formDescriptor);
                $this->removeRemberMeOption($formDescriptor);
                break;
            case AuthManager::ACTION_CREATE:
                $this->removeDefaultCreationFields($reqs, $formDescriptor);
                break;
            case AuthManager::ACTION_LINK:
                $this->disableAuthButton($reqs, $formDescriptor);
                break;
            default:
        }
    }

    public function sortLoginButton(&$formDescriptor)
    {
        if (isset($formDescriptor['discourseauth'])) {
            $formDescriptor['discourseauth']['weight'] = 101;
        }
    }

    public function removeRemberMeOption(&$formDescriptor)
    {
        // TODO: capitable with the defautl remberMe option from AuthManager
        if (!$GLOBALS['wgDiscourseConnectEnableLocalLogin']) {
            unset($formDescriptor['rememberMe']);
        }
    }


    public function removeDefaultCreationFields($reqs, &$formDescriptor)
    {
        if ($GLOBALS['wgDiscourseConnectEnableLocalLogin']) {
            return;
        }
        $req = Request\DiscourseAccountCreationRequest::getRequest($reqs);
        if (!$req) {
            return;
        }
        // keep our customize fields only
        $preservedFields = array_keys($req->getFieldInfo());
        $formDescriptor = array_filter(
            $formDescriptor,
            function ($key) use ($preservedFields) {
                return in_array($key, $preservedFields);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    public function disableAuthButton($reqs, &$formDescriptor)
    {
        $req = Request\DiscourseAuthRequest::getRequest($reqs);
        if (!$req || !$req->disabled) {
            return;
        }
        $buttonName = $req->getName();
        if (isset($formDescriptor[$buttonName])) {
            $formDescriptor[$buttonName]['disabled'] = true;
        }
    }
}

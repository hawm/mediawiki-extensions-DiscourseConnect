<?php

namespace DiscourseConnect\Hook;

use MediaWiki\SpecialPage\Hook\AuthChangeFormFieldsHook;
use MediaWiki\Auth\AuthManager;
use DiscourseConnect\Request;

class CustomizeAuthForm implements AuthChangeFormFieldsHook {
    public function onAuthChangeFormFields(
        $reqs, $fieldInfo, &$formDescriptor, $action
        ){
            switch($action){
                case AuthManager::ACTION_LOGIN:
                    $this->resortLoginButton($formDescriptor);
                    $this->removeRemberMeOption($formDescriptor);
                    break;
                case AuthManager::ACTION_CREATE:
                    $this->removeDefaultCreationFields($reqs, $formDescriptor);
                    break;
            }
    }

    public function resortLoginButton(&$formDescriptor){
        if(isset($formDescriptor['discourseconnectlogin'])){
            $formDescriptor['discourseconnectlogin']['weight'] = 101;
        }
    }

    public function removeRemberMeOption(&$formDescriptor){
        // TODO: capitable with the defautl remberMe option from AuthManager
        if(!$GLOBALS['wgDiscourseConnectEnableLocalLogin']){
            unset($formDescriptor['rememberMe']);
        }
    }


    public function removeDefaultCreationFields($reqs, &$formDescriptor){
        if($GLOBALS['wgDiscourseConnectEnableLocalLogin']){
            return;
        }
        $request = Request\DiscourseBeginPrimaryAccountCreationRequest::getRequest($reqs);
        if(!$request){
            return;
        }
        // keep our customize fields only
        $preservedFields = array_keys($request->getFieldInfo());
        $formDescriptor = array_filter(
            $formDescriptor,
            function ($key) use($preservedFields){
                return in_array($key, $preservedFields);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

}
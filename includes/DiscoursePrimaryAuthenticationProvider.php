<?php

namespace DiscourseConnect;

use MediaWiki\Auth\AbstractPrimaryAuthenticationProvider;
use MediaWiki\Auth\UserDataAuthenticationRequest;
use MediaWiki\Auth\AuthenticationResponse;
use MediaWiki\Auth\AuthenticationRequest;
use MediaWiki\Auth\UsernameAuthenticationRequest;
use MediaWiki\Auth\AuthManager;
use Mediawiki\MediaWikiServices;


// TODO: ensure DiscourseConnectConsumer
// TODO: ensure schema
// TODO: catch possible exception throw
// TODO: support user properties groups, email, realname
class DiscoursePrimaryAuthenticationProvider extends AbstractPrimaryAuthenticationProvider{
    const USERDATA_SESSION_KEY = 'DiscourseUserData';

    protected function getDiscourseConnectConsumer(){
        return DiscourseServices::getDiscourseConnectConsumer();
    }

    protected function getDiscourseUserService(){
        return DiscourseServices::getDiscourseUserService();
    }

    protected function getDiscourseGroupService(){
        return DiscourseServices::getDiscourseGroupService();
    }

    protected function getUserFactory(){
        return MediaWikiServices::getInstance()->getUserFactory();
    }


    public function getAuthenticationRequests($action, array $options){
        // add login form to Special::UserLogin
        switch($action){
            case AuthManager::ACTION_LOGIN:
                return [
                    new Request\DiscourseBeginPrimaryAuthenticationRequest()
                ];
            case AuthManager::ACTION_CREATE:
                if(!$this->config->get('DiscourseConnectEnableLocalLogin')){
                    return [
                        new Request\DiscourseBeginPrimaryAccountCreationRequest()
                    ];
                }
            default:
                return [];
        }
    }

    // authentication

    public function beginPrimaryAuthentication( array $reqs ){
        // handle form submission what we return by getAuthenticationRequests
        $request = Request\DiscourseBeginPrimaryAuthenticationRequest::getRequest($reqs);
        if(!$request){
            return AuthenticationResponse::newAbstain();
        }
        $user_login_page = \SpecialPage::getTitleFor('Userlogin');
        $return_to_url = $user_login_page->getSubpage('return')->getFullURL();
        $encoded_return_to_url = urlencode($return_to_url); // support non-ascii url
        /* 
        * Token is not necessary for DiscourseConnect provider but Mediawiki AuthManager.
        * Token be use to fit with the CSRF protection in default login flow at Special:UserLogin
        * which one of the AuthManagerSpecialPage family.
        * Maybe we can hide the token by load it from AuthenticationRequest->loadFromSubmission.
        * Token be pre-encode cause of the `+` sign in url encode/decode implementation 
        * are different between Mediawiki AuthManager(PHP urlencode/decode) and 
        * DiscourseConnect(Ruby URI lib).
        * If we redirect to a custom SpecialPage which not inherit from anyone of the
        * AuthManagerSpecialPage family rather the default Special:UserLogin,
        * then we would manual process just what we need, we don't need token. see
        * https://github.com/wikimedia/mediawiki-extensions-PluggableAuth/blob/master/includes/PluggableAuthPrimaryAuthenticationProvider.php
        */ 
        global $wgRequest;
        $token = $wgRequest->getSession()->getToken('', 'login');
        $encodedToken = str_replace('+', '%252B', $token);
        $return_to_url_with_token = "$encoded_return_to_url?wpLoginToken=$encodedToken";
        $consumer = $this->getDiscourseConnectConsumer();
        $nonce = null;
        return AuthenticationResponse::newRedirect(
            [new Request\DiscourseReturnAuthenticationRequest()],
            $consumer->getAuthUrl($return_to_url_with_token)
        );
        // TODO: handle the `return_to` query
    }

    public function continuePrimaryAuthentication( array $reqs ){
        if ($request = Request\DiscourseReturnAuthenticationRequest::getRequest($reqs)){
            // accept Discourse return
            $userdata = null;
            if (!$this->getDiscourseConnectConsumer()->loadAuthData(
                $request->sso, $request->sig, $userdata)){
                return AuthenticationResponse::newFail(
                    wfMessage('discourseconnect-error-cannot-validate-authentication-return')
                );
            }
            // preserve $userdata to session for late using
            $this->manager->setAuthenticationSessionData(
                self::USERDATA_SESSION_KEY, $userdata
            );
    
            $user = $this->getDiscourseUserService()->newUserFromExternalId(
                $userdata['external_id']
            );
            // When user exist
            if($user && $user->isRegistered()){
                return AuthenticationResponse::newPass($user->getName());
            }
            // TODO: find user by same email
            // When new user
            $username = $userdata['username'];
            $user = $this->getUserFactory()->newFromName($username);
        }elseif($request = Request\DiscourseUsernameAuthenticationRequest::getRequest($reqs)){
            // accept new username
            $username = $request->username;
            $user = $this->getUserFactory()->newFromName($username);
        }else{
            // somthing wrong
            return AuthenticationResponse::newFail(
                wfMessage('discourseconnect-error-no-authentication-workflow')
            );
        }

        if(!$user){
            $errorMessage = 'discourseconnect-wraning-username-invalid';
        }elseif($user->isRegistered()){
            $errorMessage = 'discourseconnect-warnning-username-exist';
        }else{
            $errorMessage = null;
        }
        if($errorMessage){
            return AuthenticationResponse::newUI(
                [new Request\DiscourseUsernameAuthenticationRequest($username)],
                wfMessage($errorMessage)
            );
        }

        return AuthenticationResponse::newPass($user->getName());
    }

    public function postAuthentication( $user, AuthenticationResponse $response ){
        // TODO: update userdata eacho login
    }

    public function testUserExists( $username, $flags =\User::READ_NORMAL ){
        // TODO: provider use to reserve username
    }

    public function testUserCanAuthenticate( $username ){
        return true;
    }

    // provider 
    
    public function providerNormalizeUsername( $username ){}

    public function providerRevokeAccessForUser( $username ){}

    public function providerAllowsPropertyChange( $property ){
    }

    public function providerAllowsAuthenticationDataChange(
        AuthenticationRequest $req, $checkData = true
    ){
        return \StatusValue::newGood('ignored');
    }

    public function providerChangeAuthenticationData( AuthenticationRequest $req ){}

    // account create

    public function accountCreationType(){
        return self::TYPE_LINK;
    }

    public function testForAccountCreation( $user, $creator, array $reqs ){
        return \StatusValue::newGood();
    }

    public function beginPrimaryAccountCreation( $user, $creator, array $reqs ){
        // we display our customize form but don't really support account creation 
        return AuthenticationResponse::newAbstain();
    }

    public function continuePrimaryAccountCreation( $user, $creator, array $reqs ){}

    public function finishAccountCreation( $user, $creator, AuthenticationResponse $response ){}

    public function postAccountCreation( $user, $creator, AuthenticationResponse $response ){}

    public function testUserForCreation( $user, $autocreate, array $options = [] ){
        return \StatusValue::newGood();
    }

    public function autoCreatedAccount( $user, $source ){
        $userdata = $this->manager->getAuthenticationSessionData(
            self::USERDATA_SESSION_KEY
        );
        $ret = $this->getDiscourseUserService()->linkUser(
            $user,
            $userdata['external_id']
        );
        if(!$ret){
            /* IMPORTANT
            * Delete the which just created user when associate failed,
            * but it may cause unknow error.
            * Whatever we need to do something otherwise username
            * will be occupy when code running reach here each time.
            */ 
            wfDebug('DiscourseConnect', "Unable associate account");
            return;
        }
    }

    // account link

    public function beginPrimaryAccountLink( $user, array $reqs ){
        // TODO
    }

    public function continuePrimaryAccountLink( $user, array $reqs ){
        // TODO
    }

    public function postAccountLink( $user, AuthenticationResponse $response ){
        // TODO
    }

}
<?php

namespace DiscourseConnect;

use DiscourseConnect\Request\DiscourseAuthRequest;
use DiscourseConnect\Request\DiscourseReturnRequest;
use MediaWiki\Auth\AbstractPrimaryAuthenticationProvider;
use MediaWiki\Auth\AuthenticationResponse;
use MediaWiki\Auth\AuthenticationRequest;
use MediaWiki\Auth\AuthManager;
use Mediawiki\User\UserNameUtils;
use SpecialPage;
use Wikimedia\Rdbms\ILoadBalancer;
use MediaWiki\User\UserFactory;

use DiscourseConnect\Service\DiscourseConnectConsumer;
use DiscourseConnect\Service\DiscourseUserService;
// TODO: ensure DiscourseConnecdisocurseConnectConsumert

// TODO: ensure schema
// TODO: catch possible exception throw
// TODO: support user properties groups, email, realname
class DiscoursePrimaryAuthenticationProvider extends AbstractPrimaryAuthenticationProvider
{
    const USERDATA_SESSION_KEY = 'DiscourseUserdata';

    protected $disocurseConnectConsumer;
    protected $discourseUserService;
    protected $userFactory;
    protected $userNameUtils;
    protected $loadBalancer;
    

    public function __construct(
        UserNameUtils $userNameUtils,
        ILoadBalancer $loadBalancer,
        UserFactory $userFactory,
        DiscourseConnectConsumer $disocurseConnectConsumer,
        DiscourseUserService $discourseUserService
    ) {
        $this->disocurseConnectConsumer = $disocurseConnectConsumer;
        $this->discourseUserService = $discourseUserService;
        $this->userFactory = $userFactory;
        $this->userNameUtils = $userNameUtils;
        $this->loadBalancer = $loadBalancer;
    }


    public function getAuthenticationRequests($action, array $options)
    {
        switch ($action) {
            case AuthManager::ACTION_LOGIN:
                return [
                    new Request\DiscourseAuthRequest(
                        'UserLogin',
                        'wpLoginToken',
                        'discourseauth',
                        false
                    )
                ];
            case AuthManager::ACTION_CREATE:
                if (!$this->config->get('DiscourseConnectEnableLocalLogin')) {
                    return [
                        new Request\DiscourseAccountCreationRequest()
                    ];
                }
            case AuthManager::ACTION_LINK:
                $linked = $this->testUserCanAuthenticate($options['username']);
                return [
                    new Request\DiscourseAuthRequest(
                        'LinkAccounts',
                        'wpAuthToken',
                        $linked ? 'discourselinked' : 'discourselink',
                        false,
                        $linked ?: false
                    )
                ];
            case AuthManager::ACTION_REMOVE:
                return $this->testUserCanAuthenticate($options['username']) ?
                    [new Request\DiscourseDeauthRequest('discoureunlink')] : [];
            default:
                return [];
        }
    }

    // authentication

    public function beginPrimaryAuthentication(array $reqs)
    {
        return $this->beginAuthentication($reqs);
    }

    public function continuePrimaryAuthentication(array $reqs)
    {
        $userdata = null;
        $error = $this->continueAuthentication($reqs, $userdata);
        if ($error) {
            return $error;
        }
        $mId = $this->discourseUserService->getUserIdByExternalId($userdata['external_id']);
        $user = $this->userFactory->newFromId($mId);
        if ($user && $user->isRegistered()) {
            // when exist linked user
            return AuthenticationResponse::newPass($user->getName());
        }
        $username = $userdata['username'];
        if ($this->testUserExists($username)) {
            // username exist but not link with the current Discoruse user,
            // we may prompt user to input a new username in the future
            return AuthenticationResponse::newFail(
                wfMessage('dc-error-username-exist')
            );
        }
        // local user will be auto-create then be link to Discourse user
        // by autoCreatedAccount()
        return AuthenticationResponse::newPass($username);
    }

    public function postAuthentication($user, AuthenticationResponse $response)
    {
    }

    public function testUserExists($username, $flags = \User::READ_NORMAL)
    {
        // copy from LocalPasswordPrimaryAuthenticationProvider::testUserExists
        $username = $this->userNameUtils->getCanonical($username, UserNameUtils::RIGOR_USABLE);
        if ($username === false) {
            return false;
        }

        list($db, $options) = \DBAccessObjectUtils::getDBOptions($flags);
        return (bool)$this->loadBalancer->getConnectionRef($db)->selectField(
            ['user'],
            'user_id',
            ['user_name' => $username],
            __METHOD__,
            $options
        );
    }

    public function testUserCanAuthenticate($username)
    {
        $username = $this->userNameUtils->getCanonical($username, UserNameUtils::RIGOR_USABLE);
        if ($username === false) {
            return false;
        }
        return (bool) $this->discourseUserService->getExternalIdByUserName($username);
    }

    // provider 

    public function providerNormalizeUsername($username)
    {
    }

    public function providerRevokeAccessForUser($username)
    {
    }

    public function providerAllowsPropertyChange($property)
    {
        return true;
    }

    public function providerAllowsAuthenticationDataChange(
        AuthenticationRequest $req,
        $checkData = true
    ) {
        // TODO: allow disable data change
        return \StatusValue::newGood();
    }

    public function providerChangeAuthenticationData(AuthenticationRequest $req)
    {
        if ($req->action == AuthManager::ACTION_CHANGE) {
            // TODO: ??
        } elseif ($req->action == AuthManager::ACTION_REMOVE) {
            // unlink user
            $user = $this->manager->getRequest()->getSession()->getUser();
            $this->discourseUserService->unlinkUserByUserId($user->getId());
        }
    }

    // account create

    public function accountCreationType()
    {
        return self::TYPE_LINK;
    }

    public function testForAccountCreation($user, $creator, array $reqs)
    {
        // we can prevent account creation here
        return \StatusValue::newGood();
    }

    public function beginPrimaryAccountCreation($user, $creator, array $reqs)
    {
        // we display our custom form but don't really support account creation 
        return AuthenticationResponse::newAbstain();
    }

    public function continuePrimaryAccountCreation($user, $creator, array $reqs)
    {
    }

    public function finishAccountCreation($user, $creator, AuthenticationResponse $response)
    {
    }

    public function postAccountCreation($user, $creator, AuthenticationResponse $response)
    {
    }

    public function testUserForCreation($user, $autocreate, array $options = [])
    {
        return \StatusValue::newGood();
    }

    public function autoCreatedAccount($user, $source)
    {
        $this->trylinkUser($user);
    }

    // account link

    public function beginPrimaryAccountLink($user, array $reqs)
    {
        return $this->beginAuthentication($reqs);
    }

    public function continuePrimaryAccountLink($user, array $reqs)
    {
        $userdata = null;
        $error = $this->continueAuthentication($reqs, $userdata);
        if ($error) {
            return $error;
        }
        $linked = $this->tryLinkUser($user, $userdata['external_id']);
        if (!$linked) {
            return AuthenticationResponse::newFail(
                new \Message('dc-error-unable-link-account')
            );
        }
        return AuthenticationResponse::newPass();
    }

    public function postAccountLink($user, AuthenticationResponse $response)
    {
        // TODO
    }

    public function beginAuthentication(array $reqs)
    {
        $req = DiscourseAuthRequest::getRequest($reqs);
        if (!$req) {
            return AuthenticationResponse::newAbstain();
        }
        $callbackTitle = SpecialPage::getTitleFor(
            $req->pageName,
            'return'
        );
        //  Token is not necessary for DiscourseConnect but Mediawiki AuthManager, 
        //  we may create our own special page to avoid it in the future.
        //  See AuthManagerSpecialPage::trySubmit, SpecialUserLogin::getToken,
        //  SpecialUserLogin::getTokenName and SpecialUserLogin::canByPassForm.

        $callbackUrl = $callbackTitle->getFullUrlForRedirect(
            $this->manager->getRequest()->getValues(
                $req->tokenName,
                'returnto',
                'returntoquery'
            )
        );
        // TODO: make token adapt with when canBypassForm return true
        // We now load token from addition form submission, when canBypassForm
        // we don't need addition form submit so we unable to load token
        return AuthenticationResponse::newRedirect(
            [new DiscourseReturnRequest()],
            $this->disocurseConnectConsumer->getAuthUrl($callbackUrl)
        );
    }

    public function continueAuthentication(array $reqs, &$userdata = null)
    {
        $req = Request\DiscourseReturnRequest::getRequest($reqs);
        if (!$req) {
            return AuthenticationResponse::newFail(
                wfMessage('dc-error-no-authentication-workflow')
            );
        }
        $userdata = $this->disocurseConnectConsumer->loadAuthData(
            $req->sso,
            $req->sig,
        );
        if (!$userdata) {
            return AuthenticationResponse::newFail(
                wfMessage('dc-error-cannot-validate-authentication-return')
            );
        }
        $this->manager->setAuthenticationSessionData(
            self::USERDATA_SESSION_KEY,
            $userdata
        );
    }

    public function tryLinkUser($user, $eId = null)
    {
        if (!$eId) {
            $userdata = $this->manager->getAuthenticationSessionData(
                self::USERDATA_SESSION_KEY
            );
            $eId = $userdata['external_id'];
        }

        return $this->discourseUserService->linkUserById($user->getId(), $eId);
    }
}

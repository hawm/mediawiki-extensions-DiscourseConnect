<?php

namespace DiscourseConnect\Service;

use Mediawiki\MediawikiServices;
use MediaWiki\Auth\AuthManager ;

// https://meta.discourse.org/t/using-discourse-as-an-identity-provider-sso-discourseconnect/32974
class DiscourseConnectConsumer {
    const NONCE_SESSION_KEY = 'DiscourseConnectNonce';
    const USERDATA_KEYS = [
        // keys that we need from userdata
    ];

    public function __construct(\Config $config, AuthManager $manager){
        $this->config = $config;
        $this->manager = $manager;
        $this->endpoint = $config->get('DiscourseConnectEndpoint');
        $this->secret = $config->get('DiscourseConnectSecret');
        // TODO: check endpoint and secret are provide
    }

    public function getAuthUrl($returnToUrl){
        $nonce = $this->getRandomNonce(true);
        $payload = "nonce=$nonce&return_sso_url=$returnToUrl";
        $base64_encoded_payload = base64_encode($payload);
        $url_encoded_payload = urlencode($base64_encoded_payload);
        $hex_signature = $this->sign($base64_encoded_payload);

        return "$this->endpoint?sso=$url_encoded_payload&sig=$hex_signature";
    }


    // TODO: maybe we can move this to 
    // DiscourseReturnAuthenticationRequest->loadFormSubmission
    public function loadAuthData($sso, $sig, &$userdata){
        // verify sso signature
        if (!$this->sign($sso) === $sig){
            wfDebug('DiscourseConnect', 'Return `sig` not match');
            return false;
        }
        // verify nonce
        parse_str(base64_decode($sso), $userdata);
        if(!$userdata['nonce'] || !$userdata['nonce'] === $this->getSessionNonce()){
            wfDebug('DiscourseConnect', 'Return `nonce` not match');
            return false;
        }
        // TODO: make sure $userdata fit our requirement
        return true;
    }

    protected function sign($sso){
        // we don't do hex-bin convert here cause of PHP return hex string by default
        return hash_hmac('sha256', $sso, $this->secret);
    }

    // TODO: using Session->getToken instead
    protected function getRandomNonce($save=false){
        $nonce = hash( 'md5', mt_rand() . time() );
        if ($save){
            // save to session
            $this->manager->setAuthenticationSessionData(
                self::NONCE_SESSION_KEY, $nonce
            );
        }
        return $nonce;
    }

    protected function getSessionNonce(){
        return $this->manager->getAuthenticationSessionData(
            self::NONCE_SESSION_KEY
        );
    }
}
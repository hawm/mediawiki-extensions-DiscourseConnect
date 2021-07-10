<?php

namespace DiscourseConnect\Service;

use Config;
use MediaWiki\Logger\LoggerFactory;

// https://meta.discourse.org/t/using-discourse-as-an-identity-provider-sso-discourseconnect/32974
class DiscourseConnectConsumer
{
    const SESSION_KEYS = [
        'nonce' => 'DiscourseConnectNonce',
        'userdata' => 'DiscourseConnectUserdata'
    ];
    const USERDATA_KEYS = [
        // keys that we need from userdata
    ];

    protected $logger;
    protected $endpoint;
    protected $secret;
    protected $session;


    function __construct(Config $config)
    {
        $this->endpoint = $config->get('DiscourseConnectEndpoint');
        $this->secret = $config->get('DiscourseConnectSecret');
        // TODO: check endpoint and secret
        $this->logger = LoggerFactory::getInstance('DiscourseConnect');
    }

    function getAuthUrl($callbackUrl)
    {
        $encodedCallbackUrl = urlencode($callbackUrl); // support non-ascii char in url
        $nonce = $this->getRandomNonce(true);
        $payload = "nonce=$nonce&return_sso_url=$encodedCallbackUrl";
        $base64_encoded_payload = base64_encode($payload);
        $url_encoded_payload = urlencode($base64_encoded_payload);
        $hex_signature = $this->sign($base64_encoded_payload);

        return "$this->endpoint?sso=$url_encoded_payload&sig=$hex_signature";
    }


    // TODO: maybe we can call this in
    // DiscourseReturnAuthenticationRequest->loadFormSubmission
    function loadAuthData($sso, $sig)
    {
        // verify signature
        if ($this->sign($sso) !== $sig) {
            $this->logger->info('Return `sig` not match');
            return false;
        }
        $userdata = null;
        parse_str(base64_decode($sso), $userdata);
        // verify nonce
        if ($userdata['nonce'] !== $this->getPersistedNonce()) {
            $this->logger->info('Return `nonce` not match');
            return false;
        }
        // TODO: make sure $userdata fit our requirements
        return $userdata;
    }

    function sign($sso)
    {
        return hash_hmac('sha256', $sso, $this->secret);
    }


    protected  function getRandomNonce($persist = false)
    {
        $nonce = hash('md5', mt_rand() . time());
        if ($persist) {
            $this->persistNonce($nonce);
        }
        return $nonce;
    }

    protected function persistNonce($nonce)
    {
        global $wgRequest;
        $response = $wgRequest->response();
        if ($response->headersSent()) {
            // Can't do anything now
            $this->logger->debug(__METHOD__ . ': Headers already sent');
            return;
        }
        $response->setcookie(
            'nonce',
            $nonce
        );
    }

    function getPersistedNonce()
    {
        global $wgRequest;
        return $wgRequest->getCookie('nonce');
    }
}

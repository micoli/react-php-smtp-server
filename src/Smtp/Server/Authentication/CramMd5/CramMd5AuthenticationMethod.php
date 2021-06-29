<?php

namespace Micoli\Smtp\Server\Authentication\CramMd5;

use Micoli\Smtp\Server\Authentication\AuthenticationDataInterface;
use Micoli\Smtp\Server\Authentication\AuthenticationMethodInterface;
use Micoli\Smtp\Server\SmtpSession\ReturnCode;
use Micoli\Smtp\Server\SmtpSession\SmtpSessionInterface;

class CramMd5AuthenticationMethod implements AuthenticationMethodInterface
{
    private string $challengeDomain;
    private HmacMd5 $hmacMd5;

    public function __construct(HmacMd5 $hmacMd5, string $challengeDomain)
    {
        $this->challengeDomain = $challengeDomain;
        $this->hmacMd5 = $hmacMd5;
    }

    public function getType(): string
    {
        return 'CRAM-MD5';
    }

    public function init(SmtpSessionInterface $connection, ?string $data): void
    {
        $challenge = $this->generateChallenge();
        $connection->setAuthenticationData(new CramMd5AuthenticationData($connection, $challenge));
        $connection->sendReply(ReturnCode::_334_CONTINUE_AUTHENTICATION_REQUEST, base64_encode($challenge));
    }

    public function handleData(SmtpSessionInterface $connection, string $data): void
    {
        $authenticationData = $connection->getAuthenticationData();
        $tokens = explode(' ', base64_decode($data));
        if (2 !== count($tokens)) {
            $connection->checkAuth();

            return;
        }
        [$username, $password] = $tokens;
        $authenticationData->setUsername($username);
        $authenticationData->setPassword($password);

        $connection->checkAuth();
    }

    public function validateIdentity(AuthenticationDataInterface $authenticationData, string $password): bool
    {
        if (null === $authenticationData->getPassword()) {
            return false;
        }

        /** @var CramMd5AuthenticationData $authenticationData */
        $digest = $this->hmacMd5->getDigest($password, $authenticationData->getChallenge());

        return $digest === $authenticationData->getPassword();
    }

    private function generateChallenge(): string
    {
        $strong = true;
        $random = openssl_random_pseudo_bytes(32, $strong);

        return sprintf('<%s@%s>', bin2hex($random), $this->challengeDomain);
    }
}

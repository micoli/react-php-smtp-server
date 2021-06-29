<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Authentication;

use Psr\Log\LoggerInterface;

final class IdentityValidator implements IdentityValidatorInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function checkAuth(AuthenticationMethodInterface $method, ?AuthenticationDataInterface $authenticationData): bool
    {
        $this->logger->debug(sprintf(
            'User AutoValidator "%s" [%s] [%s] [%s]'.PHP_EOL,
            $method->getType(),
            $authenticationData?->getUsername(),
            $authenticationData?->getPassword(),
            null === $authenticationData ? 'none' : ($method->validateIdentity($authenticationData, 'password1') ? '1' : '0')
        ));

        return true;
    }
}

<?php

namespace Micoli\Smtp\Server\Authentication;

interface IdentityValidatorInterface
{
    public function checkAuth(AuthenticationMethodInterface $method, ?AuthenticationDataInterface $authenticationData): bool;
}

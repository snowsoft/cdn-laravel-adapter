<?php

namespace CdnServices\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * CDN Services Auth Facade – kayıt, giriş, token.
 *
 * @method static array register(array $data, ?string $registrationToken = null)
 * @method static array|null login(string $email, string $password)
 * @method static array|null tokenForUser(string $userId, ?string $email = null, ?string $role = null)
 * @method static string|null getRegistrationToken()
 * @method static bool requiresRegistrationToken()
 *
 * @see \CdnServices\CdnServicesAuthService
 */
class CdnServicesAuth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cdn-services.auth';
    }
}

<?php

namespace CdnServices;

use Illuminate\Support\Facades\Http;

/**
 * CDN Services Auth – kayıt, giriş ve token işlemleri.
 * REGISTRATION_TOKEN backend'de tanımlıysa kayıt için token zorunludur.
 */
class CdnServicesAuthService
{
    protected string $baseUrl;
    protected int $timeout;
    protected ?string $registrationToken;

    public function __construct(array $config = [])
    {
        $config = $config ?: config('cdn-services', config('filesystems.disks.cdn-services', []));
        $this->baseUrl = rtrim($config['base_url'] ?? 'http://localhost:3012', '/');
        $this->timeout = (int) ($config['timeout'] ?? 30);
        $this->registrationToken = $config['registration_token'] ?? null;
        if ($this->registrationToken !== null) {
            $this->registrationToken = trim($this->registrationToken);
            if ($this->registrationToken === '') {
                $this->registrationToken = null;
            }
        }
    }

    /**
     * Yeni kullanıcı kaydı. Backend'de REGISTRATION_TOKEN varsa $registrationToken veya config'teki token kullanılır.
     *
     * @param  array{email: string, password: string, name?: string}  $data
     * @param  string|null  $registrationToken  X-Registration-Token (null ise config'ten alınır)
     * @return array{success: bool, user: array, token: string, tokenType: string, expiresIn: string}
     *
     * @throws \Illuminate\Http\Client\RequestException  HTTP hatalarında
     */
    public function register(array $data, ?string $registrationToken = null): array
    {
        $token = $registrationToken ?? $this->registrationToken;
        $body = [
            'email' => $data['email'] ?? '',
            'password' => $data['password'] ?? '',
            'name' => $data['name'] ?? null,
        ];
        if ($token !== null && $token !== '') {
            $body['registrationToken'] = $token;
        }

        $req = Http::timeout($this->timeout)->acceptJson();
        if ($token !== null && $token !== '') {
            $req = $req->withHeaders(['X-Registration-Token' => $token]);
        }
        $response = $req->post($this->baseUrl . '/api/auth/register', $body);

        if ($response->status() === 403) {
            throw new \RuntimeException(
                $response->json('error') ?? 'Kayıt için geçerli registration token gerekir'
            );
        }

        if ($response->status() === 409) {
            throw new \RuntimeException(
                $response->json('error') ?? 'Bu email adresi zaten kayıtlı'
            );
        }

        $response->throw();
        $data = $response->json();
        return [
            'success' => $data['success'] ?? true,
            'user' => $data['user'] ?? [],
            'token' => $data['token'] ?? '',
            'tokenType' => $data['tokenType'] ?? 'Bearer',
            'expiresIn' => $data['expiresIn'] ?? '',
        ];
    }

    /**
     * Email ve şifre ile giriş; JWT döner.
     *
     * @return array{success: bool, user: array, token: string, tokenType: string, expiresIn: string}|null
     */
    public function login(string $email, string $password): ?array
    {
        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->post($this->baseUrl . '/api/auth/login', [
                'email' => $email,
                'password' => $password,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $body = $response->json();
        return [
            'success' => $body['success'] ?? true,
            'user' => $body['user'] ?? [],
            'token' => $body['token'] ?? '',
            'tokenType' => $body['tokenType'] ?? 'Bearer',
            'expiresIn' => $body['expiresIn'] ?? '',
        ];
    }

    /**
     * Belirtilen kullanıcı için JWT üretir (backend POST /api/auth/token). Sunucu tarafında CDN işlemleri için token almakta kullanılır.
     *
     * @return array{success: bool, token: string, tokenType: string, expiresIn: string, user: array}|null
     */
    public function tokenForUser(string $userId, ?string $email = null, ?string $role = null): ?array
    {
        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->post($this->baseUrl . '/api/auth/token', array_filter([
                'userId' => $userId,
                'email' => $email,
                'role' => $role,
            ]));

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Kayıt için config'teki registration token'ı döner (varsa). Boş/null ise backend'de token zorunlu değildir.
     */
    public function getRegistrationToken(): ?string
    {
        return $this->registrationToken;
    }

    /**
     * Kayıt için token zorunlu mu (config'te registration_token tanımlı mı).
     */
    public function requiresRegistrationToken(): bool
    {
        return $this->registrationToken !== null && $this->registrationToken !== '';
    }
}

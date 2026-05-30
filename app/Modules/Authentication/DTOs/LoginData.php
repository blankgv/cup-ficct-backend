<?php

namespace App\Modules\Authentication\DTOs;

// Lleva las credenciales del Request al Service.
final readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: (string) $data['email'],
            password: (string) $data['password'],
        );
    }

    /**
     * @return array{email: string, password: string}
     */
    public function toCredentials(): array
    {
        return ['email' => $this->email, 'password' => $this->password];
    }
}

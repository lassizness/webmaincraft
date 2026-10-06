<?php

declare(strict_types=1);
namespace common\services;
final class GravitPasswordHasher
{
    public static function hash(string $password): string
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        if ($hash === false) { throw new \RuntimeException('Unable to hash password'); }
        // PHP emits $2y$; Gravit/BouncyCastle works most predictably with $2b$.
        if (str_starts_with($hash, '$2y$')) { $hash = '$2b$' . substr($hash, 4); }
        return $hash;
    }
    public static function verify(string $password, string $hash): bool { return password_verify($password, $hash); }
}

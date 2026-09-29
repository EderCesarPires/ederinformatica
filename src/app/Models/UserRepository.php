<?php
declare(strict_types=1);

namespace App\Models;

final class UserRepository extends Repository
{
    protected function table(): string
    {
        return 'users';
    }

    protected function fillable(): array
    {
        return ['name', 'email', 'password'];
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }
}

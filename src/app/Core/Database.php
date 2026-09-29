<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * PADRÃO DE PROJETO: Singleton
 *
 * Garante uma única conexão PDO com o MySQL durante toda a requisição.
 * O construtor é privado e a clonagem/desserialização são bloqueadas.
 */
final class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            Config::get('db.host'),
            Config::get('db.port'),
            Config::get('db.name')
        );

        $this->pdo = new PDO($dsn, Config::get('db.user'), Config::get('db.pass'), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \LogicException('Não é possível desserializar um Singleton.');
    }
}

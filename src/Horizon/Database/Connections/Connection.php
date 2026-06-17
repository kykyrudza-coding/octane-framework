<?php

declare(strict_types=1);

namespace Horizon\Database\Connections;

use Closure;
use Horizon\Contracts\Database\Connections\ConnectionContract;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final class Connection implements ConnectionContract
{
    private int $transactionDepth = 0;

    private array $queryLog = [];

    private bool $loggingEnabled = false;

    public function __construct(
        private PDO $pdo,
        private readonly string $name,
        private readonly string $driverName,
    ) {}

    public function select(string $query, array $bindings = []): array
    {
        return $this->run($query, $bindings, function (PDO $pdo, string $query, array $bindings): array {
            $statement = $pdo->prepare($query);
            $statement->execute($bindings);

            return $statement->fetchAll();
        });
    }

    public function insert(string $query, array $bindings = []): bool
    {
        return $this->run($query, $bindings, function (PDO $pdo, string $query, array $bindings): bool {
            $statement = $pdo->prepare($query);

            return $statement->execute($bindings);
        });
    }

    public function update(string $query, array $bindings = []): int
    {
        return $this->run($query, $bindings, function (PDO $pdo, string $query, array $bindings): int {
            $statement = $pdo->prepare($query);
            $statement->execute($bindings);

            return $statement->rowCount();
        });
    }

    public function delete(string $query, array $bindings = []): int
    {
        return $this->run($query, $bindings, function (PDO $pdo, string $query, array $bindings): int {
            $statement = $pdo->prepare($query);
            $statement->execute($bindings);

            return $statement->rowCount();
        });
    }

    public function raw(string $query, array $bindings = []): mixed
    {
        return $this->run($query, $bindings, function (PDO $pdo, string $query, array $bindings): mixed {
            if (empty($bindings)) {
                return $pdo->exec($query);
            }

            $statement = $pdo->prepare($query);
            $statement->execute($bindings);

            return $statement;
        });
    }

    /**
     * @throws Throwable
     */
    public function transaction(Closure $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();

            return $result;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function beginTransaction(): void
    {
        if ($this->transactionDepth === 0) {
            $this->pdo->beginTransaction();
        } else {
            $this->savepoint("sp{$this->transactionDepth}");
        }

        $this->transactionDepth++;
    }

    public function commit(): void
    {
        $this->transactionDepth--;

        if ($this->transactionDepth === 0) {
            $this->pdo->commit();
        }
    }

    public function rollback(): void
    {
        $this->transactionDepth--;

        if ($this->transactionDepth === 0) {
            $this->pdo->rollBack();
        } else {
            $this->rollbackTo("sp{$this->transactionDepth}");
        }
    }

    public function savepoint(string $name): void
    {
        $this->pdo->exec("SAVEPOINT {$name}");
    }

    public function rollbackTo(string $name): void
    {
        $this->pdo->exec("ROLLBACK TO SAVEPOINT {$name}");
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDriverName(): string
    {
        return $this->driverName;
    }

    public function enableQueryLog(): void
    {
        $this->loggingEnabled = true;
    }

    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function setPdo(PDO $pdo): void
    {
        $this->pdo = $pdo;
    }

    private function run(string $query, array $bindings, Closure $callback): mixed
    {
        $start = microtime(true);

        try {
            $result = $callback($this->pdo, $query, $bindings);
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Database query failed: {$e->getMessage()}\nQuery: {$query}",
                previous: $e,
            );
        }

        if ($this->loggingEnabled) {
            $this->queryLog[] = [
                'query'    => $query,
                'bindings' => $bindings,
                'time_ms'  => round((microtime(true) - $start) * 1000, 2),
            ];
        }

        return $result;
    }
}

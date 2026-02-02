<?php

namespace App\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\SQLParserUtils;
use Doctrine\DBAL\Types\Type;
use Throwable;

class PointWrapper extends Connection
{
    public function __construct(array $params, Driver $driver, Configuration $config, EventManager $eventManager)
    {
        parent::__construct($params, $driver, $config, $eventManager);
    }

    public function prepare($sql)
    {
        try {
            $stmt = new Statement($sql, $this);
            if ($this->getDatabasePlatform() instanceof MySqlPlatform) {
                $stmt->connexion = $this;
            }
        } catch (Throwable $e) {
            $this->handleExceptionDuringQuery($e, $sql);
        }

        return $stmt;
    }

    /**
     * Executes an SQL statement with the given parameters and returns the number of affected rows.
     *
     * Could be used for:
     *  - DML statements: INSERT, UPDATE, DELETE, etc.
     *  - DDL statements: CREATE, DROP, ALTER, etc.
     *  - DCL statements: GRANT, REVOKE, etc.
     *  - Session control statements: ALTER SESSION, SET, DECLARE, etc.
     *  - Other statements that don't yield a row set.
     *
     * This method supports PDO binding types as well as DBAL mapping types.
     *
     * @param string                                                               $sql    SQL statement
     * @param array<int, mixed>|array<string, mixed>                               $params Statement parameters
     * @param array<int, int|string|Type|null>|array<string, int|string|Type|null> $types  Parameter types
     *
     * @return int|string the number of affected rows
     *
     * @throws Exception
     */
    public function executeStatement($sql, array $params = [], array $types = [])
    {
        try {
            if ($params) {
                [$sql, $params, $types] = SQLParserUtils::expandListParameters($sql, $params, $types);

                $stmt = $this->prepare($sql);

                if ($types) {
                    $this->bindTypedValues($stmt, $params, $types);
                    $stmt->execute();
                } else {
                    $stmt->execute($params);
                }

                $result = $stmt->rowCount();
            } else {
                $result = $this->exec($sql);
            }
        } catch (Throwable $e) {
            $this->handleExceptionDuringQuery(
                $e,
                $sql,
                $params,
                $types
            );
        }

        return $result;
    }

    /**
     * Binds a set of parameters, some or all of which are typed with a PDO binding type
     * or DBAL mapping type, to a given statement.
     *
     * @internal duck-typing used on the $stmt parameter to support driver statements as well as
     *           raw PDOStatement instances
     *
     * @param Driver\Statement                                                     $stmt   Prepared statement
     * @param array<int, mixed>|array<string, mixed>                               $params Statement parameters
     * @param array<int, int|string|Type|null>|array<string, int|string|Type|null> $types  Parameter types
     *
     * @return void
     *
     * This is based on bindTypedValues
     */
    private function bindTypedValues($stmt, array $params, array $types)
    {
        // Check whether parameters are positional or named. Mixing is not allowed, just like in PDO.
        if (is_int(key($params))) {
            // Positional parameters
            $typeOffset = array_key_exists(0, $types) ? -1 : 0;
            $bindIndex = 1;
            foreach ($params as $value) {
                $typeIndex = $bindIndex + $typeOffset;
                if (isset($types[$typeIndex])) {
                    $type = $types[$typeIndex];
                    [$value, $bindingType] = $this->getBindingInfo($value, $type);
                    $stmt->bindValue($bindIndex, $value, $bindingType);
                } else {
                    $stmt->bindValue($bindIndex, $value);
                }

                ++$bindIndex;
            }
        } else {
            // Named parameters
            foreach ($params as $name => $value) {
                if (isset($types[$name])) {
                    $type = $types[$name];
                    [$value, $bindingType] = $this->getBindingInfo($value, $type);
                    $stmt->bindValue($name, $value, $bindingType);
                } else {
                    $stmt->bindValue($name, $value);
                }
            }
        }
    }

    /**
     * Gets the binding type of a given type. The given type can be a PDO or DBAL mapping type.
     *
     * @param mixed                $value the value to bind
     * @param int|string|Type|null $type  the type to bind (PDO or DBAL)
     *
     * @return array{mixed, int} [0] => the (escaped) value, [1] => the binding type
     */
    private function getBindingInfo($value, $type): array
    {
        if (is_string($type)) {
            $type = Type::getType($type);
        }

        if ($type instanceof Type) {
            $value = $type->convertToDatabaseValue($value, $this->getDatabasePlatform());
            $bindingType = $type->getBindingType();
        } else {
            $bindingType = $type ?? ParameterType::STRING;
        }

        return [$value, $bindingType];
    }
}

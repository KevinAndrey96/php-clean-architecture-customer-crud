<?php

declare(strict_types=1);

namespace CustomerCrud\Infrastructure\Persistence;

use CustomerCrud\Domain\Customer\Address;
use CustomerCrud\Domain\Customer\City;
use CustomerCrud\Domain\Customer\CompanyName;
use CustomerCrud\Domain\Customer\CreditLimit;
use CustomerCrud\Domain\Customer\Customer;
use CustomerCrud\Domain\Customer\CustomerNotFound;
use CustomerCrud\Domain\Customer\CustomerRepository;
use CustomerCrud\Domain\Customer\DuplicateNit;
use CustomerCrud\Domain\Customer\Nit;
use CustomerCrud\Domain\Customer\PhoneNumber;
use PDO;
use PDOException;
use PDOStatement;
use UnexpectedValueException;

/**
 * Prepared statements only and SQL kept to the subset MySQL 8 and SQLite share, so the same
 * class is exercised by the fast in-memory integration tests and the Docker stack.
 */
final class PdoCustomerRepository implements CustomerRepository
{
    private const INTEGRITY_VIOLATION = '23000';
    private const LIKE_ESCAPE = '!';
    private const COLUMNS = 'nit, check_digit, company_name, address, phone, city, credit_limit';

    public function __construct(private readonly PDO $pdo)
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function add(Customer $customer): void
    {
        $this->write(
            $customer->nit,
            'INSERT INTO customers ('.self::COLUMNS.') VALUES (:nit, :check_digit, :company_name, :address, :phone, :city, :credit_limit)',
            $this->bindings($customer),
        );
    }

    public function replace(Nit $current, Customer $customer): void
    {
        $this->get($current);

        $this->write(
            $customer->nit,
            'UPDATE customers SET nit = :nit, check_digit = :check_digit, company_name = :company_name, address = :address,'
            .' phone = :phone, city = :city, credit_limit = :credit_limit, updated_at = CURRENT_TIMESTAMP WHERE nit = :current',
            $this->bindings($customer) + ['current' => $current->body()],
        );
    }

    public function remove(Nit $nit): void
    {
        $statement = $this->pdo->prepare('DELETE FROM customers WHERE nit = :nit');
        $statement->execute(['nit' => $nit->body()]);

        if ($statement->rowCount() === 0) {
            throw CustomerNotFound::withNit($nit->formatted());
        }
    }

    public function get(Nit $nit): Customer
    {
        $statement = $this->pdo->prepare('SELECT '.self::COLUMNS.' FROM customers WHERE nit = :nit');
        $statement->execute(['nit' => $nit->body()]);
        $rows = $this->rows($statement);

        if ($rows === []) {
            throw CustomerNotFound::withNit($nit->formatted());
        }

        return $this->hydrate($rows[0]);
    }

    public function search(string $term): array
    {
        $sql = 'SELECT '.self::COLUMNS.' FROM customers';
        $params = [];

        if ($term !== '') {
            $sql .= " WHERE nit LIKE :by_nit ESCAPE '!' OR LOWER(company_name) LIKE :by_name ESCAPE '!' OR LOWER(city) LIKE :by_city ESCAPE '!'";
            $pattern = '%'.$this->escapeLike(mb_strtolower($term)).'%';
            $params = ['by_nit' => $pattern, 'by_name' => $pattern, 'by_city' => $pattern];
        }

        $statement = $this->pdo->prepare($sql.' ORDER BY LOWER(company_name), nit');
        $statement->execute($params);

        return array_map($this->hydrate(...), $this->rows($statement));
    }

    /**
     * @param array<string, int|string> $params
     *
     * @throws DuplicateNit
     */
    private function write(Nit $nit, string $sql, array $params): void
    {
        try {
            $this->pdo->prepare($sql)->execute($params);
        } catch (PDOException $e) {
            if ($e->getCode() === self::INTEGRITY_VIOLATION) {
                throw DuplicateNit::of($nit, $e);
            }

            throw $e;
        }
    }

    /**
     * @return array<string, int|string>
     */
    private function bindings(Customer $customer): array
    {
        return [
            'nit' => $customer->nit->body(),
            'check_digit' => $customer->nit->checkDigit(),
            'company_name' => $customer->companyName->value(),
            'address' => $customer->address->value(),
            'phone' => $customer->phone->digits(),
            'city' => $customer->city->value(),
            'credit_limit' => $customer->creditLimit->pesos(),
        ];
    }

    private function escapeLike(string $term): string
    {
        return strtr($term, [
            self::LIKE_ESCAPE => self::LIKE_ESCAPE.self::LIKE_ESCAPE,
            '%' => self::LIKE_ESCAPE.'%',
            '_' => self::LIKE_ESCAPE.'_',
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rows(PDOStatement $statement): array
    {
        /** @var list<array<string, mixed>> */
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Customer
    {
        return new Customer(
            Nit::fromString($this->column($row, 'nit').'-'.$this->column($row, 'check_digit')),
            CompanyName::fromString($this->column($row, 'company_name')),
            Address::fromString($this->column($row, 'address')),
            PhoneNumber::fromString($this->column($row, 'phone')),
            City::fromString($this->column($row, 'city')),
            CreditLimit::fromPesos((int) $this->column($row, 'credit_limit')),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function column(array $row, string $name): string
    {
        $value = $row[$name] ?? null;
        if (!is_scalar($value)) {
            throw new UnexpectedValueException(sprintf('customers.%s: unexpected value', $name));
        }

        return (string) $value;
    }
}

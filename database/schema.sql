-- Portable between MySQL 8 (InnoDB, utf8mb4 by default) and SQLite, which runs the
-- integration tests. The NIT body is the natural key; the check digit is stored so
-- reports and exports never need to recompute it.
CREATE TABLE customers (
    nit          VARCHAR(15)  NOT NULL PRIMARY KEY,
    check_digit  SMALLINT     NOT NULL,
    company_name VARCHAR(120) NOT NULL,
    address      VARCHAR(160) NOT NULL,
    phone        CHAR(10)     NOT NULL,
    city         VARCHAR(80)  NOT NULL,
    credit_limit BIGINT       NOT NULL DEFAULT 0,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (check_digit BETWEEN 0 AND 9),
    CHECK (credit_limit >= 0)
);

CREATE INDEX idx_customers_company_name ON customers (company_name);
CREATE INDEX idx_customers_city ON customers (city);

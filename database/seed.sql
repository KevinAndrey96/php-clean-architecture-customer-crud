-- Fictitious companies for local demos. Check digits are valid DIAN modulo 11 values.
-- The versioned comment makes the MySQL init client read this file as UTF-8; SQLite skips it.
/*!40101 SET NAMES utf8mb4 */;

INSERT INTO customers (nit, check_digit, company_name, address, phone, city, credit_limit) VALUES
    ('900123456', 8, 'Andes Logística S.A.S.', 'Calle 26 # 68C-61, Oficina 402', '6017456321', 'Bogotá', 45000000),
    ('901234567', 7, 'Café Cordillera Ltda.', 'Carrera 23 # 64B-33', '6068812345', 'Manizales', 12500000),
    ('830045678', 8, 'Ingeniería Caribe S.A.', 'Vía 40 # 79B-06, Bodega 3', '6053698741', 'Barranquilla', 80000000),
    ('811022233', 6, 'Textiles del Valle de Aburrá S.A.S.', 'Carrera 48 # 10-45', '3104567890', 'Medellín', 0);

-- دروستکردنی داتابەیس ئەگەر بوونی نەبێت
CREATE DATABASE IF NOT EXISTS pos_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pos_system;

-- ١. زیادکردنی ستوونی پێویست بۆ خشتەی کاڵاکان (products)
ALTER TABLE products 
ADD COLUMN IF NOT EXISTS default_warranty VARCHAR(100) DEFAULT '',
ADD COLUMN IF NOT EXISTS min_stock DECIMAL(15, 2) NOT NULL DEFAULT 5,
ADD COLUMN IF NOT EXISTS expiry_date DATE NULL DEFAULT NULL;

-- ٢. زیادکردنی کاتی واسڵکردن و بەرواری ئیستحقاقی قەرز/قیست بۆ وەسڵی فرۆشتن (sales)
ALTER TABLE sales 
ADD COLUMN IF NOT EXISTS due_date DATE NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS paid_at DATETIME NULL DEFAULT NULL;

-- ٣. خشتەی مێژووی واسڵکردنی وەسڵەکان (بۆ بەڵگە و بینینی کاتی هەر واسڵکردنێک)
CREATE TABLE IF NOT EXISTS debt_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    customer_id INT NULL,
    paid_amount_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    usd_rate DECIMAL(10, 2) NOT NULL DEFAULT 1500.00,
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    note VARCHAR(255) DEFAULT '',
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- ١. خشتەی ڕێکخستنەکان (settings)
-- ==========================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY,
    shop_name VARCHAR(150) NOT NULL DEFAULT 'مارکێت و کاشێری BMS',
    usd_rate DECIMAL(10, 2) NOT NULL DEFAULT 1500.00,
    active_currency ENUM('IQD', 'USD') NOT NULL DEFAULT 'IQD'
);

INSERT IGNORE INTO settings (id, shop_name, usd_rate, active_currency) VALUES
(1, 'مارکێت و کاشێری BMS', 1500.00, 'IQD');

-- ==========================================================
-- ٢. خشتەی بەکارهێنەران / کاشێر (users)
-- ==========================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL
);

INSERT IGNORE INTO users (id, username, password, full_name) VALUES
(1, 'admin', '1234', 'بەڕێوەبەری گشتی');

-- ==========================================================
-- ٣. خشتەی پۆلەکان / بەشەکان (categories)
-- ==========================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE
);

-- ==========================================================
-- ٤. خشتەی براندەکان / مارکەکان (brands)
-- ==========================================================
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE
);

-- ==========================================================
-- ٥. خشتەی کاڵاکان / کۆگا (products) بە هەموو ستوونە نوێیەکانەوە
-- ==========================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barcode VARCHAR(100) UNIQUE,
    name VARCHAR(200) NOT NULL,
    buy_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    sell_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    wholesale_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    special_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    company_name VARCHAR(150) DEFAULT '',
    brand VARCHAR(150) DEFAULT '',
    category VARCHAR(150) DEFAULT '',
    stock DECIMAL(15, 2) NOT NULL DEFAULT 0
);

-- ==========================================================
-- ٦. خشتەی کڕیارەکان (customers) بە ناونیشان و ڕەقەمی کەفیلەوە
-- ==========================================================
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(50) DEFAULT '',
    address VARCHAR(250) DEFAULT '',
    guarantor VARCHAR(150) DEFAULT '',
    guarantor_phone VARCHAR(50) DEFAULT '',
    balance_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0
);

-- ==========================================================
-- ٧. خشتەی وەسڵەکانی فرۆشتن (sales)
-- ==========================================================
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NULL,
    customer_name VARCHAR(150) NOT NULL DEFAULT 'کڕیاری گشتی',
    customer_phone VARCHAR(50) DEFAULT '',
    guarantor_name VARCHAR(150) DEFAULT '',
    sale_type ENUM('cash', 'debt', 'installment', 'quote') NOT NULL DEFAULT 'cash',
    subtotal_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    discount_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    total_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    paid_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    change_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    remaining_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    usd_rate DECIMAL(10, 2) NOT NULL DEFAULT 1500.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- ٨. خشتەی کاڵاکانی ناو وەسڵی فرۆشتن (sale_items)
-- ==========================================================
CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NULL,
    barcode VARCHAR(100) DEFAULT '',
    item_name VARCHAR(200) NOT NULL,
    serial_number VARCHAR(150) DEFAULT '',
    warranty VARCHAR(100) DEFAULT '',
    is_external TINYINT(1) NOT NULL DEFAULT 0,
    is_gift TINYINT(1) NOT NULL DEFAULT 0,
    qty DECIMAL(15, 2) NOT NULL DEFAULT 1,
    buy_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    sell_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    total_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE
);

-- ==========================================================
-- ٩. خشتەی کۆمپانیاکان (companies)
-- ==========================================================
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(50) DEFAULT '',
    address VARCHAR(250) DEFAULT '',
    note VARCHAR(250) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- ١٠. خشتەی وەسڵە کڕدراوەکان (purchases)
-- ==========================================================
CREATE TABLE IF NOT EXISTS purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(100) NOT NULL DEFAULT '',
    company_id INT NULL,
    company_name VARCHAR(150) NOT NULL,
    purchase_type ENUM('cash', 'debt') NOT NULL DEFAULT 'cash',
    purchase_date DATE NOT NULL,
    subtotal_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    discount_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    total_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    paid_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    remaining_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    usd_rate DECIMAL(10, 2) NOT NULL DEFAULT 1500.00,
    note VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- ١١. خشتەی کاڵاکانی ناو وەسڵی کڕین (purchase_items)
-- ==========================================================
CREATE TABLE IF NOT EXISTS purchase_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT NOT NULL,
    product_id INT NOT NULL,
    barcode VARCHAR(100) DEFAULT '',
    item_name VARCHAR(200) NOT NULL,
    qty DECIMAL(15, 2) NOT NULL DEFAULT 1,
    old_stock DECIMAL(15, 2) NOT NULL DEFAULT 0,
    old_buy_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    buy_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    avg_buy_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    sell_price_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    total_iqd DECIMAL(15, 2) NOT NULL DEFAULT 0,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE
);



-- ==========================================================
-- ١٢. داخڵکردنی داتای نموونەیی بۆ تاقیکردنەوە
-- ==========================================================
INSERT IGNORE INTO categories (name) VALUES ('دانەوێڵە و برنج'), ('زەیت و ڕۆن'), ('چا و شەکر'), ('شیرەمەنی');
INSERT IGNORE INTO brands (name) VALUES ('مەحموود'), ('جیهان'), ('نیدۆ');

INSERT IGNORE INTO products (barcode, name, buy_price_iqd, sell_price_iqd, wholesale_price_iqd, special_price_iqd, company_name, brand, category, stock) VALUES
('1001', 'برنجی مەحموود ١٠ کیلۆ', 18000, 21000, 20000, 19500, 'کۆمپانیای مەحموود', 'مەحموود', 'دانەوێڵە و برنج', 50),
('1002', 'زەیتی گوڵەبەڕۆژە ١ لیتر', 2000, 2500, 2350, 2250, 'کۆمپانیای جیهان', 'جیهان', 'زەیت و ڕۆن', 120),
('1003', 'چای جیهان ١ کیلۆ', 9000, 11000, 10500, 10000, 'کۆمپانیای جیهان', 'جیهان', 'چا و شەکر', 40),
('1004', 'شەکری سپی ٥ کیلۆ', 6000, 7500, 7000, 6800, 'کۆمپانیای جیهان', '', 'چا و شەکر', 80),
('1005', 'شیری نیدۆ قتووی گەورە', 19500, 22500, 21500, 21000, 'کۆمپانیای جیهان', 'نیدۆ', 'شیرەمەنی', 30);

INSERT INTO customers (name, phone, address, guarantor, guarantor_phone, balance_iqd) VALUES
('ئارام محەمەد', '07701234567', 'سلێمانی - بەختیاری', 'کاروان عەلی', '07701119988', 0),
('سەرکەوت ئەحمەد', '07509876543', 'هەولێر - شۆڕش', 'هێمن جەلال', '07502223344', 45000),
('دانا کەریم', '07712223344', 'کەلار - شەهیدان', 'ئاسۆ حەسەن', '07715556677', 0);

INSERT INTO companies (name, phone, address, note) VALUES
('کۆمپانیای جیهان', '07701112233', 'سلێمانی - ناوچەی پیشەسازی', 'مەوادی خۆراک'),
('کۆمپانیای مەحموود', '07504445566', 'هەولێر - ڕێگای گوێڕ', 'تایبەت بە برنج و دانەوێڵە');
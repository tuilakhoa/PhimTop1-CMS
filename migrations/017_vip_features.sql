-- Add VIP features to members table
ALTER TABLE members ADD COLUMN is_vip TINYINT(1) DEFAULT 0 AFTER role;
ALTER TABLE members ADD COLUMN vip_until DATETIME NULL AFTER is_vip;
ALTER TABLE members ADD COLUMN total_donated INT DEFAULT 0 AFTER vip_until;

-- Create table for donation/payment transactions
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    amount INT NOT NULL,
    transaction_code VARCHAR(100) UNIQUE NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    type VARCHAR(50) DEFAULT 'donate',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

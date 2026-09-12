-- Schema fuer das kuratierte Marktverzeichnis (MariaDB/MySQL)

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lebenszyklus eines Eintrags:
-- pending_payment -> (Stripe-Zahlung erfolgreich, per Webhook) -> pending_review -> published | rejected
-- Es gibt bewusst keinen Weg an payment_status = 'paid' vorbei zu status = 'published'.
CREATE TABLE IF NOT EXISTS markets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  category ENUM('kuerbismarkt','weihnachtsmarkt','wochenmarkt','flohmarkt','bauernmarkt','sonstiges') NOT NULL,
  description TEXT NOT NULL,
  street VARCHAR(190) NOT NULL,
  postal_code VARCHAR(20) NOT NULL,
  city VARCHAR(120) NOT NULL,
  latitude DECIMAL(9,6) NOT NULL,
  longitude DECIMAL(9,6) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  opening_hours VARCHAR(190) NULL,
  website VARCHAR(255) NULL,
  contact_name VARCHAR(190) NOT NULL,
  contact_email VARCHAR(190) NOT NULL,

  payment_status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
  price_amount INT NOT NULL COMMENT 'in Cent',
  price_currency VARCHAR(10) NOT NULL DEFAULT 'eur',
  stripe_session_id VARCHAR(255) NULL,
  stripe_payment_intent_id VARCHAR(255) NULL,

  status ENUM('pending_payment','pending_review','published','rejected') NOT NULL DEFAULT 'pending_payment',
  rejection_reason VARCHAR(255) NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_status (status),
  INDEX idx_category (category),
  INDEX idx_city (city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE DATABASE IF NOT EXISTS helping_hands
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE helping_hands;

DROP TABLE IF EXISTS users;
CREATE TABLE IF NOT EXISTS users (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100)        NOT NULL,
  email       VARCHAR(150)        NOT NULL UNIQUE,
  mobile      VARCHAR(15)         NOT NULL,
  password    VARCHAR(255)        NOT NULL,
  bio         TEXT,
  city        VARCHAR(100),
  profile_pic VARCHAR(300),
  is_active   TINYINT(1)          NOT NULL DEFAULT 0,
  role        ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  created_at  DATETIME            NOT NULL,
  updated_at  DATETIME            ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS email_verifications (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  otp_code    VARCHAR(6)   NOT NULL,
  expires_at  DATETIME     NOT NULL,
  created_at  DATETIME     NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS donations (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED,
  category    ENUM('clothes','stationery','toys','charity') NOT NULL,
  user_name   VARCHAR(100)        NOT NULL,
  mobile      VARCHAR(15)         NOT NULL,
  address     TEXT                NOT NULL,
  description TEXT,
  image_path  VARCHAR(300),
  status      ENUM('pending','confirmed','picked_up','delivered','cancelled')
              NOT NULL DEFAULT 'pending',
  pickup_date DATE,
  notes       TEXT,
  created_at  DATETIME            NOT NULL,
  INDEX idx_user    (user_id),
  INDEX idx_category(category),
  INDEX idx_status  (status),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feedback (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100)        NOT NULL,
  email       VARCHAR(150)        NOT NULL,
  rating      TINYINT             NOT NULL DEFAULT 5 CHECK (rating BETWEEN 1 AND 5),
  category    VARCHAR(100),
  message     TEXT                NOT NULL,
  recommend   ENUM('yes','no')    NOT NULL DEFAULT 'yes',
  is_published TINYINT(1)         NOT NULL DEFAULT 0,
  created_at  DATETIME            NOT NULL,
  INDEX idx_rating (rating)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contact_messages (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100)        NOT NULL,
  email       VARCHAR(150)        NOT NULL,
  phone       VARCHAR(20),
  subject     VARCHAR(200),
  message     TEXT                NOT NULL,
  is_read     TINYINT(1)          NOT NULL DEFAULT 0,
  replied_at  DATETIME,
  created_at  DATETIME            NOT NULL,
  INDEX idx_read (is_read)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS help_requests (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  category    VARCHAR(50)         NOT NULL,
  items       TEXT                NOT NULL,
  reason      TEXT                NOT NULL,
  status      ENUM('pending','approved','fulfilled','rejected') 
              NOT NULL DEFAULT 'pending',
  created_at  DATETIME            NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS distributions (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_id  INT UNSIGNED NOT NULL,
  donation_id INT UNSIGNED,
  admin_id    INT UNSIGNED,
  proof_image VARCHAR(300),
  notes       TEXT,
  distributed_at DATETIME         NOT NULL,
  FOREIGN KEY (request_id) REFERENCES help_requests(id) ON DELETE CASCADE,
  FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE SET NULL,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO users (name, email, mobile, password, is_active, role, created_at) VALUES
  ('Admin User', 'Khiratkarriya@gmail.com', '9876543210', 
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'admin', NOW());

INSERT INTO users (name, email, mobile, password, bio, city, created_at) VALUES
  ('Priya Sharma',   'priya@example.com',  '9876543210',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'Passionate about giving back to the community.', 'nagpur', NOW()),
  ('Rahul Verma',    'rahul@example.com',  '9123456789',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'Regular donor of clothes and stationery.', 'nagpur', NOW()),
  ('Ananya Iyer',    'ananya@example.com', '8765432109',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'Books and stationery donor for school children.', 'nagpur', NOW());


INSERT INTO donations (user_id, category, user_name, mobile, address, description, status, created_at) VALUES
  (1, 'clothes',     'Priya Sharma',  '9876543210', 'jaitala nagpur440036', '5 shirts, 3 jeans, 2 jackets',   'picked_up',  NOW()),
  (1, 'toys',        'Priya Sharma',  '9876543210', 'mangalmurti nagpur440036', 'Lego set, board games, puzzles', 'delivered',  DATE_SUB(NOW(), INTERVAL 5 DAY)),
  (2, 'stationery',  'Rahul Verma',   '9123456789', 'jaripatka nagpur 440014', '20 notebooks, 10 textbooks',  'confirmed',  DATE_SUB(NOW(), INTERVAL 2 DAY)),
  (3, 'charity',     'Ananya Iyer',   '8765432109', 'reshimbagh nagpur 440009', 'Household items, utensils',  'pending',    NOW());

INSERT INTO feedback (name, email, rating, message, recommend, is_published, created_at) VALUES
  ('Meera Nair',      'meera@example.com',   5, 'Incredible service! Pickup was on time.', 'yes', 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
  ('Sanjay Kulkarni', 'sanjay@example.com',  5, 'So easy to use. Donated toys same day.',  'yes', 1, DATE_SUB(NOW(), INTERVAL 1 DAY));


CREATE OR REPLACE VIEW v_user_donations AS
  SELECT
    u.id         AS user_id,
    u.name       AS user_name,
    u.email,
    COUNT(d.id)  AS total_donations,
    SUM(d.category = 'clothes')    AS clothes_count,
    SUM(d.category = 'toys')       AS toys_count,
    SUM(d.category = 'stationery') AS stationery_count,
    SUM(d.category = 'charity')    AS charity_count
  FROM users u
  LEFT JOIN donations d ON d.user_id = u.id
  GROUP BY u.id;


CREATE OR REPLACE VIEW v_published_feedback AS
  SELECT name, rating, message, created_at
  FROM feedback
  WHERE is_published = 1
  ORDER BY created_at DESC;

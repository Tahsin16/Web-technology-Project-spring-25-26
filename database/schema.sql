-- Recipe Sharing Platform — Full Schema
CREATE DATABASE IF NOT EXISTS recipe_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE recipe_platform;

CREATE TABLE IF NOT EXISTS users (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name             VARCHAR(100)  NOT NULL,
  email            VARCHAR(180)  NOT NULL UNIQUE,
  password_hash    VARCHAR(255)  NOT NULL,
  bio              TEXT,
  profile_pic_path VARCHAR(300)  DEFAULT NULL,
  dietary_prefs    JSON,
  role             ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (name) VALUES
  ('Italian'),('Mexican'),('Thai'),('Indian'),('Japanese'),
  ('Chinese'),('French'),('American'),('Mediterranean'),('Middle Eastern');

CREATE TABLE IF NOT EXISTS recipes (
  id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  author_id           INT UNSIGNED NOT NULL,
  category_id         INT UNSIGNED NOT NULL,
  title               VARCHAR(200) NOT NULL,
  description         TEXT,
  diet_type           ENUM('Any','Vegetarian','Vegan','Gluten-Free','Keto') NOT NULL DEFAULT 'Any',
  difficulty          ENUM('easy','medium','hard') NOT NULL DEFAULT 'easy',
  prep_time_mins      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  cook_time_mins      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  servings            TINYINT UNSIGNED NOT NULL DEFAULT 1,
  featured_image_path VARCHAR(300) DEFAULT NULL,
  status              ENUM('draft','published') NOT NULL DEFAULT 'published',
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id)   REFERENCES users(id)      ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ingredients (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  recipe_id   INT UNSIGNED NOT NULL,
  name        VARCHAR(150) NOT NULL,
  quantity    VARCHAR(50),
  unit        VARCHAR(50),
  order_index SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS steps (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  recipe_id   INT UNSIGNED NOT NULL,
  instruction TEXT NOT NULL,
  step_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookmarks (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  recipe_id  INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_bookmark (user_id, recipe_id),
  FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
  FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reviews (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  recipe_id   INT UNSIGNED NOT NULL,
  user_id     INT UNSIGNED NOT NULL,
  rating      TINYINT UNSIGNED NOT NULL,
  review_text TEXT,
  reply_text  TEXT DEFAULT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_review (recipe_id, user_id),
  FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
  CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

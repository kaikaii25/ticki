-- Nothing Ticketing System: Full Database Schema

-- Drop tables if they exist (in correct order to avoid FK errors)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS attachments;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS ticket_comments;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS canned_responses;
DROP TABLE IF EXISTS audit_logs;
SET FOREIGN_KEY_CHECKS = 1;

-- Table: departments
CREATE TABLE departments (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: users
CREATE TABLE users (
  id INT(11) NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  role ENUM('admin','user','agent','manager') DEFAULT 'user',
  department_id INT(11) NOT NULL,
  is_admin TINYINT(1) DEFAULT 0,
  status ENUM('active','inactive') DEFAULT 'active',
  last_login TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY department_id (department_id),
  CONSTRAINT users_ibfk_1 FOREIGN KEY (department_id) REFERENCES departments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: tickets
CREATE TABLE tickets (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  status ENUM('open','in_progress','resolved','closed','on_hold') DEFAULT 'open',
  priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
  created_by INT(11) NOT NULL,
  department_id INT(11) NOT NULL,
  assigned_department_id INT(11) DEFAULT NULL,
  due_date DATE DEFAULT NULL,
  closed_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  attachment VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY created_by (created_by),
  KEY department_id (department_id),
  KEY assigned_department_id (assigned_department_id),
  CONSTRAINT tickets_ibfk_1 FOREIGN KEY (created_by) REFERENCES users(id),
  CONSTRAINT tickets_ibfk_2 FOREIGN KEY (department_id) REFERENCES departments(id),
  CONSTRAINT tickets_ibfk_3 FOREIGN KEY (assigned_department_id) REFERENCES departments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: ticket_comments
CREATE TABLE ticket_comments (
  id INT(11) NOT NULL AUTO_INCREMENT,
  ticket_id INT(11) NOT NULL,
  user_id INT(11) NOT NULL,
  comment TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY ticket_id (ticket_id),
  KEY user_id (user_id),
  CONSTRAINT ticket_comments_ibfk_1 FOREIGN KEY (ticket_id) REFERENCES tickets(id),
  CONSTRAINT ticket_comments_ibfk_2 FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: notifications
CREATE TABLE notifications (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  ticket_id INT(11) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY ticket_id (ticket_id),
  CONSTRAINT notifications_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT notifications_ibfk_2 FOREIGN KEY (ticket_id) REFERENCES tickets(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: canned_responses
CREATE TABLE canned_responses (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(100) NOT NULL,
  content TEXT NOT NULL,
  created_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

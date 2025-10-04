CREATE DATABASE adzdiag;
USE adzdiag;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255)
);

-- Test login: admin@example.com / admin
INSERT INTO users (username, password) VALUES ('admin@example.com', MD5('admin'));

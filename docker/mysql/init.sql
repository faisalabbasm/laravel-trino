-- Create sample database and tables for testing
CREATE DATABASE IF NOT EXISTS test_db;

USE test_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    department VARCHAR(100),
    salary DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    price DECIMAL(10, 2),
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO users (name, email, department, salary) VALUES
    ('John Doe', 'john@example.com', 'Engineering', 75000.00),
    ('Jane Smith', 'jane@example.com', 'Marketing', 65000.00),
    ('Bob Johnson', 'bob@example.com', 'Engineering', 80000.00),
    ('Alice Williams', 'alice@example.com', 'Sales', 70000.00),
    ('Charlie Brown', 'charlie@example.com', 'HR', 60000.00);

INSERT INTO products (name, category, price, stock) VALUES
    ('Laptop', 'Electronics', 999.99, 50),
    ('Mouse', 'Electronics', 29.99, 200),
    ('Keyboard', 'Electronics', 79.99, 150),
    ('Monitor', 'Electronics', 299.99, 75),
    ('Desk Chair', 'Furniture', 199.99, 30);


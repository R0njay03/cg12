-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS pcg_training;
USE pcg_training;

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    description TEXT,
    duration VARCHAR(100),
    capacity INT DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create personnel table
CREATE TABLE IF NOT EXISTS personnel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    rank VARCHAR(100) NOT NULL,
    unit VARCHAR(255) NOT NULL,
    course_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
);

-- Create users table for admin login
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample courses (ignore duplicates)
INSERT IGNORE INTO courses (course_name, description, duration, capacity) VALUES
('Advanced Navigation', 'Advanced maritime navigation techniques and GPS systems', '4 weeks', 25),
('Radio Communication', 'Maritime radio communication protocols and emergency procedures', '2 weeks', 30),
('Search and Rescue Operations', 'Comprehensive SAR operations training including helicopter and boat operations', '6 weeks', 20),
('Maritime Law Enforcement', 'Legal procedures and enforcement techniques for maritime operations', '3 weeks', 25),
('Emergency Medical Response', 'First aid and emergency medical procedures for maritime environments', '2 weeks', 35),
('Vessel Inspection Procedures', 'Standard operating procedures for vessel safety inspections', '3 weeks', 20);

-- Insert sample personnel (ignore duplicates)
INSERT IGNORE INTO personnel (name, rank, unit, course_id) VALUES
('LTJG Juan Dela Cruz', 'Lieutenant Junior Grade', 'CG District NCR', 1),
('PO3 Maria Santos', 'Petty Officer 3', 'CG Station Batangas', 2),
('LT Carlos Rodriguez', 'Lieutenant', 'CG District Visayas', 3),
('PO2 Ana Reyes', 'Petty Officer 2', 'CG Station Cebu', 4),
('CDR Roberto Fernandez', 'Commander', 'CG Headquarters', 5),
('SN Jose Garcia', 'Seaman', 'CG Station Manila', 6),
('LTJG Patricia Villanueva', 'Lieutenant Junior Grade', 'CG District Mindanao', 1),
('CPO Miguel Torres', 'Chief Petty Officer', 'CG Station Davao', 2);

-- Insert default admin user (password: admin123) - ignore if exists
INSERT IGNORE INTO users (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Update personnel table structure for PCG CG-12 Training System
USE pcg_training;

-- Drop existing personnel table and recreate with new structure
DROP TABLE IF EXISTS personnel;

-- Create new personnel table with enhanced structure
CREATE TABLE personnel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rank VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    firstname VARCHAR(100) NOT NULL,
    unit_code VARCHAR(50) NOT NULL,
    category ENUM('Officer', 'Non-Officer') NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_personnel (lastname, firstname, unit_code)
);

-- Insert sample personnel data
INSERT INTO personnel (rank, lastname, firstname, unit_code, category, remarks) VALUES
-- Officers
('Commander', 'Fernandez', 'Roberto', 'CG-HQ', 'Officer', 'Headquarters Staff'),
('Lieutenant', 'Rodriguez', 'Carlos', 'CG-DV', 'Officer', 'District Visayas'),
('Lieutenant Junior Grade', 'Dela Cruz', 'Juan', 'CG-NCR', 'Officer', 'District NCR'),
('Lieutenant Junior Grade', 'Villanueva', 'Patricia', 'CG-DM', 'Officer', 'District Mindanao'),

-- Non-Officers
('Chief Petty Officer', 'Torres', 'Miguel', 'CG-SD', 'Non-Officer', 'Station Davao'),
('Petty Officer 3', 'Santos', 'Maria', 'CG-SB', 'Non-Officer', 'Station Batangas'),
('Petty Officer 2', 'Reyes', 'Ana', 'CG-SC', 'Non-Officer', 'Station Cebu'),
('Seaman', 'Garcia', 'Jose', 'CG-SM', 'Non-Officer', 'Station Manila'),
('Petty Officer 1', 'Lopez', 'Antonio', 'CG-SP', 'Non-Officer', 'Station Palawan'),
('Seaman Apprentice', 'Martinez', 'Carmen', 'CG-SZ', 'Non-Officer', 'Station Zamboanga'); 
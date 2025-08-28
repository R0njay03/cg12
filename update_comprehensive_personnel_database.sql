-- Update personnel table structure for comprehensive PCG CG-12 Training System
USE pcg_training;

-- Drop existing personnel table and recreate with comprehensive structure
DROP TABLE IF EXISTS personnel;

-- Create new comprehensive personnel table
CREATE TABLE personnel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Basic Information (Common for both Officers and Non-Officers)
    rank VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    firstname VARCHAR(100) NOT NULL,
    mi VARCHAR(10), -- Middle Initial
    serial_number VARCHAR(50) NOT NULL,
    unit_code VARCHAR(50) NOT NULL,
    sub_unit VARCHAR(100),
    category ENUM('Officer', 'Non-Officer') NOT NULL,
    
    -- Non-Officer Specific Fields
    cgmc_cgnoc_class VARCHAR(100), -- CGMC/CGNOC Class
    specialization VARCHAR(200), -- SPECIALIZATION
    functional_course VARCHAR(200), -- FUNCTIONAL
    blmc_almc_cgnoac VARCHAR(100), -- BLMC/ALMC/CGNOAC
    cgnosec VARCHAR(100), -- CGNOSEC
    original_enlistment DATE, -- Original Enlistment
    date_entered_service DATE, -- Date Entered the Service
    comp_ret VARCHAR(50), -- CompRet
    last_promotion_date DATE, -- Last Date of Promotion
    
    -- Officer Specific Fields
    cgoc_class VARCHAR(100), -- CGOC Class
    cgscc_class VARCHAR(100), -- Coast Guard Station Commanders Course Class
    cgsc_class VARCHAR(100), -- Coast Guard Staff Course
    cgec_class VARCHAR(100), -- Coast Guard Officer Senior Executive Course
    third_level_career VARCHAR(100), -- Third Level Career Course
    
    -- Common Advanced Fields
    seminars_workshops TEXT, -- Seminars/Workshop Attended
    upload_file VARCHAR(255), -- File attachment path
    remarks TEXT,
    
    -- System Fields
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Unique constraint
    UNIQUE KEY unique_personnel (serial_number)
);

-- Insert sample Non-Officer personnel data
INSERT INTO personnel (
    rank, lastname, firstname, mi, serial_number, unit_code, sub_unit, category,
    cgmc_cgnoc_class, specialization, functional_course, blmc_almc_cgnoac, cgnosec,
    original_enlistment, date_entered_service, comp_ret, last_promotion_date,
    seminars_workshops, remarks
) VALUES
('Chief Petty Officer', 'Torres', 'Miguel', 'A', 'CG-2020-001', 'CG-SD', 'Operations Division', 'Non-Officer',
'CGMC Class 2020-01', 'Operations Specialization', 'Maritime Safety Administration', 'BLMC Class 2019-02', 'CGNOSEC Class 2021-01',
'2015-03-15', '2015-03-15', 'Active', '2023-06-15',
'Maritime Security Workshop 2023, Search and Rescue Training 2022, Environmental Protection Seminar 2021',
'Experienced operations specialist with strong leadership skills'),

('Petty Officer 3', 'Santos', 'Maria', 'B', 'CG-2021-002', 'CG-SB', 'Technical Division', 'Non-Officer',
'CGNOC Class 2021-03', 'Machinery Technician', 'Marine Environment Protection', 'ALMC Class 2020-01', NULL,
'2018-07-20', '2018-07-20', 'Active', '2022-12-10',
'Engine Maintenance Workshop 2023, Pollution Response Training 2022',
'Skilled machinery technician with environmental protection expertise'),

('Petty Officer 2', 'Reyes', 'Ana', 'C', 'CG-2022-003', 'CG-SC', 'Administrative Division', 'Non-Officer',
'CGMC Class 2022-02', 'Yeoman Specialization', 'Internal Auditor Course', NULL, NULL,
'2019-11-10', '2019-11-10', 'Active', '2023-03-20',
'Administrative Procedures Workshop 2023, Records Management Training 2022',
'Dedicated administrative specialist with audit experience'),

('Seaman', 'Garcia', 'Jose', 'D', 'CG-2023-004', 'CG-SM', 'Deck Division', 'Non-Officer',
'CGNOC Class 2023-01', 'Boatswains Mate', 'Maritime Security Law Enforcement', NULL, NULL,
'2020-05-12', '2020-05-12', 'Active', '2023-09-15',
'Deck Operations Training 2023, Security Procedures Workshop 2022',
'New seaman showing excellent potential in deck operations'),

('Petty Officer 1', 'Lopez', 'Antonio', 'E', 'CG-2021-005', 'CG-SP', 'Communications Division', 'Non-Officer',
'CGMC Class 2021-04', 'Radio Operations', 'Radio Communication Course', NULL, NULL,
'2017-09-08', '2017-09-08', 'Active', '2022-08-25',
'Advanced Radio Operations 2023, Emergency Communications Training 2022',
'Skilled radio operator with emergency response experience');

-- Insert sample Officer personnel data
INSERT INTO personnel (
    rank, lastname, firstname, mi, serial_number, unit_code, sub_unit, category,
    cgoc_class, cgscc_class, cgsc_class, cgec_class, third_level_career,
    seminars_workshops, remarks
) VALUES
('Commander', 'Fernandez', 'Roberto', 'F', 'CG-2010-001', 'CG-HQ', 'Operations Directorate', 'Officer',
'CGOC Class 2010-01', 'CGSCC Class 2018-01', 'CGSC Class 2015-02', 'CGEC Class 2022-01', 'Third Level Career Course 2020',
'Strategic Leadership Seminar 2023, International Maritime Law Conference 2022, Command and Control Workshop 2021',
'Senior officer with extensive command experience and strategic planning expertise'),

('Lieutenant', 'Rodriguez', 'Carlos', 'G', 'CG-2015-002', 'CG-DV', 'District Operations', 'Officer',
'CGOC Class 2015-03', 'CGSCC Class 2021-02', 'CGSC Class 2019-01', NULL, NULL,
'District Operations Management 2023, Maritime Law Enforcement Training 2022, Staff Planning Workshop 2021',
'Experienced district operations officer with strong management skills'),

('Lieutenant Junior Grade', 'Dela Cruz', 'Juan', 'H', 'CG-2018-003', 'CG-NCR', 'Station Operations', 'Officer',
'CGOC Class 2018-02', NULL, 'CGSC Class 2022-01', NULL, NULL,
'Station Command Training 2023, Personnel Management Workshop 2022, Operational Planning Seminar 2021',
'Young officer showing excellent leadership potential in station operations'),

('Lieutenant Junior Grade', 'Villanueva', 'Patricia', 'I', 'CG-2019-004', 'CG-DM', 'Technical Operations', 'Officer',
'CGOC Class 2019-01', NULL, NULL, NULL, NULL,
'Technical Operations Management 2023, Engineering Systems Workshop 2022, Technical Planning Seminar 2021',
'Technical officer with strong engineering background and operational expertise'),

('Lieutenant', 'Martinez', 'Carmen', 'J', 'CG-2016-005', 'CG-HQ', 'Staff Operations', 'Officer',
'CGOC Class 2016-02', 'CGSCC Class 2022-03', 'CGSC Class 2020-02', NULL, NULL,
'Staff Operations Management 2023, Strategic Planning Workshop 2022, Policy Development Seminar 2021',
'Staff officer with excellent planning and policy development skills'); 
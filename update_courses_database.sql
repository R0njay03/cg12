-- Update courses database structure for PCG CG-12 Training System
USE pcg_training;

-- Drop existing courses table and recreate with new structure
DROP TABLE IF EXISTS courses;

-- Create new courses table with enhanced structure
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    course_code VARCHAR(50),
    training_type ENUM('Local Training', 'Foreign Training', 'Unit / Interagency Training') NOT NULL,
    category VARCHAR(100),
    subcategory VARCHAR(100),
    target_audience ENUM('Officer', 'Non-Officer', 'Both') NOT NULL,
    course_level ENUM('Basic', 'Intermediate', 'Advanced', 'Senior') DEFAULT 'Basic',
    description TEXT,
    duration VARCHAR(100),
    capacity INT DEFAULT 30,
    prerequisites TEXT,
    learning_objectives TEXT,
    course_outline TEXT,
    file_attachment VARCHAR(255), -- For PDF/image files
    status ENUM('Active', 'Inactive', 'Pending') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert Local Training Courses

-- NON-OFFICER SPECIALIZATION RATING COURSES
INSERT INTO courses (course_name, course_code, training_type, category, subcategory, target_audience, course_level, description, duration, capacity, prerequisites, learning_objectives) VALUES

-- Career Courses - Specialization Courses
('Operations Specialization Rating Course', 'OSRC-001', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Comprehensive training program designed to develop operational expertise in Coast Guard personnel, covering maritime operations, search and rescue procedures, and operational planning.', '8 weeks', 25, 'Minimum 2 years service, Basic seamanship certification', 'Develop advanced operational skills, Master SAR procedures, Understand operational planning and execution'),

('Boatswains Mate Rating Course', 'BMRC-002', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Specialized training for boatswains mates covering seamanship, boat handling, deck operations, and crew supervision responsibilities.', '6 weeks', 20, 'Basic seamanship course completion, Physical fitness standards', 'Master boat handling techniques, Develop leadership skills, Understand deck operations and maintenance'),

('Machinery Technician Rating Course', 'MTRC-003', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Technical training program for machinery technicians covering marine engines, mechanical systems, troubleshooting, and maintenance procedures.', '10 weeks', 15, 'Basic mechanical aptitude, Technical school background preferred', 'Master marine engine operations, Develop troubleshooting skills, Understand preventive maintenance procedures'),

('Electricians Mate Rating Course', 'EMRC-004', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Electrical systems training covering marine electrical systems, electronics, power distribution, and electrical safety procedures.', '8 weeks', 18, 'Basic electrical knowledge, Safety certification', 'Master electrical system operations, Develop diagnostic skills, Understand electrical safety protocols'),

('Damage Control Rating Course', 'DCRC-005', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Critical training in damage control procedures, firefighting, flooding control, and emergency response aboard vessels.', '4 weeks', 30, 'Basic firefighting certification, Physical fitness requirements', 'Master damage control procedures, Develop emergency response skills, Understand vessel safety systems'),

('Commissary Specialization Rating Course', 'CSRC-006', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Basic', 'Training in food service operations, inventory management, nutrition, and galley operations for Coast Guard facilities.', '6 weeks', 20, 'Food safety certification, Basic math skills', 'Master food service operations, Develop inventory management skills, Understand nutrition and meal planning'),

('Aids to Navigation Specialization Course', 'ATONSC-007', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Specialized training in maintenance and operation of navigational aids, buoy systems, and lighthouse operations.', '8 weeks', 15, 'Basic navigation knowledge, Technical aptitude', 'Master ATON maintenance procedures, Develop technical skills, Understand navigation systems'),

('Radio Operations and Maintenance Specialization Course', 'ROMSC-008', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Comprehensive training in radio communications, equipment maintenance, and emergency communication procedures.', '6 weeks', 25, 'Basic electronics knowledge, Communication skills', 'Master radio operations, Develop maintenance skills, Understand emergency communication protocols'),

('Gunners Mate Specialization Course', 'GMSC-009', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Advanced', 'Advanced training in weapons systems, ammunition handling, ballistics, and weapons maintenance procedures.', '10 weeks', 12, 'Security clearance, Physical fitness standards, Weapons safety certification', 'Master weapons systems operations, Develop maintenance expertise, Understand ballistics and safety procedures'),

('Information System Technician Specialization Course', 'ISTSC-010', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Technical training in computer systems, network administration, cybersecurity, and information technology support.', '12 weeks', 20, 'Basic computer skills, Technical aptitude test', 'Master IT system operations, Develop cybersecurity skills, Understand network administration'),

('Coast Guard Security Border Protection Specialization Course', 'CGSBPSC-011', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Advanced', 'Specialized training in border security, immigration enforcement, customs procedures, and security protocols.', '8 weeks', 25, 'Security clearance, Law enforcement background check', 'Master border security procedures, Develop enforcement skills, Understand legal frameworks'),

('Coast Guard Intelligence Course', 'CGIC-012', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Advanced', 'Intelligence analysis training covering threat assessment, information gathering, analysis techniques, and intelligence reporting.', '10 weeks', 15, 'Top secret clearance, Analytical skills assessment', 'Master intelligence analysis, Develop threat assessment skills, Understand reporting procedures'),

('CGIG-IAS Investigation and Inspection Specialization Course', 'CGIG-IAS-013', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Advanced', 'Specialized training in investigation techniques, inspection procedures, evidence handling, and report writing.', '8 weeks', 18, 'Law enforcement background, Investigation experience preferred', 'Master investigation techniques, Develop inspection skills, Understand legal procedures'),

('Internal Auditor Specialization Course', 'IASC-014', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Training in audit procedures, financial analysis, compliance checking, and audit reporting for internal operations.', '6 weeks', 20, 'Accounting background, Analytical skills', 'Master audit procedures, Develop analytical skills, Understand compliance requirements'),

('Marine Environmental Protection Specialization Course', 'MEPSC-015', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Environmental protection training covering pollution response, environmental law, and marine ecosystem protection.', '8 weeks', 25, 'Environmental science background preferred', 'Master environmental protection procedures, Develop response skills, Understand environmental regulations'),

('Yeoman Specialization Course', 'YSC-016', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Basic', 'Administrative training covering office procedures, records management, correspondence, and administrative support functions.', '4 weeks', 30, 'Basic computer skills, Communication skills', 'Master administrative procedures, Develop organizational skills, Understand records management'),

('Paralegal Specialization Course', 'PSC-017', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Legal support training covering legal research, document preparation, case management, and court procedures.', '10 weeks', 15, 'College education preferred, Legal interest', 'Master legal research skills, Develop document preparation abilities, Understand legal procedures'),

('Veterinary Aide Specialization Course', 'VASC-018', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Basic', 'Training in animal care, veterinary assistance, health monitoring, and basic medical procedures for service animals.', '6 weeks', 12, 'Animal handling experience, Medical interest', 'Master animal care procedures, Develop medical assistance skills, Understand health monitoring'),

('Drill Instructor Specialization Course', 'DISC-019', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Advanced', 'Leadership training for drill instructors covering training methods, leadership techniques, and recruit development.', '8 weeks', 15, 'Leadership experience, Physical fitness standards, Minimum 5 years service', 'Master training techniques, Develop leadership skills, Understand recruit development'),

('Healthcare Technician Specialization Course', 'HTSC-020', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Medical training covering basic healthcare, first aid, medical procedures, and health maintenance for Coast Guard personnel.', '12 weeks', 20, 'Medical background preferred, First aid certification', 'Master healthcare procedures, Develop medical skills, Understand health maintenance');

-- FUNCTIONAL COURSES
INSERT INTO courses (course_name, course_code, training_type, category, subcategory, target_audience, course_level, description, duration, capacity, prerequisites, learning_objectives) VALUES

('Maritime Safety Administration Course (MARSAD)', 'MARSAD-021', 'Local Training', 'Functional Courses', 'Maritime Safety', 'Both', 'Intermediate', 'Comprehensive course covering maritime safety regulations, vessel inspection procedures, safety management systems, and accident investigation.', '6 weeks', 25, 'Basic maritime knowledge, Safety certification', 'Master safety regulations, Develop inspection skills, Understand safety management systems'),

('Marine Environment Protection Course (MAREP)', 'MAREP-022', 'Local Training', 'Functional Courses', 'Environmental Protection', 'Both', 'Intermediate', 'Environmental protection course covering marine pollution prevention, response procedures, environmental law, and ecosystem protection.', '4 weeks', 30, 'Environmental awareness, Basic science knowledge', 'Master pollution prevention, Develop response capabilities, Understand environmental law'),

('Maritime Security Law Enforcement Course (MARSEC)', 'MARSEC-023', 'Local Training', 'Functional Courses', 'Security and Law Enforcement', 'Both', 'Advanced', 'Law enforcement training covering maritime security, boarding procedures, law enforcement techniques, and legal authorities.', '8 weeks', 20, 'Law enforcement background, Security clearance', 'Master security procedures, Develop enforcement skills, Understand legal authorities');

-- SENIOR LEVEL COURSES (Non-Officer)
INSERT INTO courses (course_name, course_code, training_type, category, subcategory, target_audience, course_level, description, duration, capacity, prerequisites, learning_objectives) VALUES

('Coast Guard Non-Officer Advanced Course (CGNOAC)', 'CGNOAC-024', 'Local Training', 'Senior Level Courses', 'Advanced Leadership', 'Non-Officer', 'Advanced', 'Advanced leadership course for senior non-commissioned officers covering strategic thinking, advanced leadership, and organizational management.', '10 weeks', 15, 'Minimum 10 years service, Leadership experience, Supervisory role', 'Develop strategic thinking, Master advanced leadership, Understand organizational management'),

('Coast Guard Senior Executive Course (CGNOSEC)', 'CGNOSEC-025', 'Local Training', 'Senior Level Courses', 'Executive Leadership', 'Non-Officer', 'Senior', 'Executive-level course for senior non-commissioned officers covering executive leadership, policy development, and strategic management.', '8 weeks', 12, 'CGNOAC completion, Minimum 15 years service, Executive potential', 'Master executive leadership, Develop policy skills, Understand strategic management');

-- OFFICER COURSES
INSERT INTO courses (course_name, course_code, training_type, category, subcategory, target_audience, course_level, description, duration, capacity, prerequisites, learning_objectives) VALUES

('Coast Guard Officer Course (Basic Course)', 'CGOC-026', 'Local Training', 'Officer Courses', 'Basic Officer Training', 'Officer', 'Basic', 'Foundational course for new Coast Guard officers covering leadership fundamentals, maritime law, navigation, and basic command responsibilities.', '16 weeks', 30, 'Officer commission, College degree, Physical fitness standards', 'Develop basic leadership skills, Master maritime fundamentals, Understand command responsibilities'),

('Coast Guard Station Commanders Course', 'CGSCC-027', 'Local Training', 'Officer Courses', 'Command Training', 'Officer', 'Advanced', 'Command training for station commanders covering operational command, resource management, personnel leadership, and community relations.', '8 weeks', 20, 'Minimum 5 years officer service, Command selection', 'Master command skills, Develop management abilities, Understand operational leadership'),

('Coast Guard Staff Course', 'CGSC-028', 'Local Training', 'Officer Courses', 'Staff Training', 'Officer', 'Intermediate', 'Staff officer training covering staff procedures, planning processes, coordination techniques, and analytical skills for headquarters assignments.', '12 weeks', 25, 'Minimum 3 years officer service, Staff assignment', 'Master staff procedures, Develop analytical skills, Understand planning processes'),

('Coast Guard Executive Course', 'CGEC-029', 'Local Training', 'Officer Courses', 'Executive Training', 'Officer', 'Senior', 'Executive leadership course for senior officers covering strategic leadership, policy development, interagency coordination, and executive decision-making.', '10 weeks', 15, 'Minimum 12 years officer service, Senior leadership potential', 'Master executive leadership, Develop strategic thinking, Understand policy development');

-- Sample Foreign Training and Unit/Interagency Training entries
INSERT INTO courses (course_name, course_code, training_type, category, target_audience, course_level, description, duration, capacity, file_attachment) VALUES

('International Maritime Security Course', 'IMSC-030', 'Foreign Training', 'International Cooperation', 'Both', 'Advanced', 'International course conducted in partnership with allied coast guards focusing on maritime security cooperation and joint operations.', '4 weeks', 10, 'foreign_training_maritime_security.pdf'),

('Joint Interagency Task Force Training', 'JIATF-031', 'Unit / Interagency Training', 'Joint Operations', 'Both', 'Advanced', 'Multi-agency training exercise focusing on coordination between Coast Guard, Navy, and other agencies in joint operations.', '2 weeks', 50, 'interagency_joint_ops.pdf'),

('ASEAN Coast Guard Cooperation Course', 'ACGCC-032', 'Foreign Training', 'Regional Cooperation', 'Officer', 'Advanced', 'Regional cooperation course with ASEAN member coast guards focusing on maritime domain awareness and regional security.', '3 weeks', 15, 'asean_cooperation_course.pdf');
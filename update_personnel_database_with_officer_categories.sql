-- Update personnel table to include new officer categories
USE pcg_training;

-- Update the category ENUM to include new officer types
ALTER TABLE personnel MODIFY COLUMN category ENUM('Officer', 'Non-Officer', 'General Line Officer', 'Technical Officer') NOT NULL;

-- Update existing Officer records to be more specific
UPDATE personnel SET category = 'General Line Officer' WHERE category = 'Officer' AND (cgoc_class IS NOT NULL OR cgscc_class IS NOT NULL OR cgsc_class IS NOT NULL);

-- Update remaining Officer records to Technical Officer if they have technical training
UPDATE personnel SET category = 'Technical Officer' WHERE category = 'Officer' AND (specialization IS NOT NULL OR functional_course IS NOT NULL);

-- Add any remaining Officer records as General Line Officer (default)
UPDATE personnel SET category = 'General Line Officer' WHERE category = 'Officer';

-- Verify the changes
SELECT category, COUNT(*) as count FROM personnel GROUP BY category; 
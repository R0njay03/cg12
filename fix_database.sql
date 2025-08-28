-- Fix database structure for PCG CG-12 Training System
USE pcg_training;

-- Add status column if it doesn't exist
ALTER TABLE courses ADD COLUMN IF NOT EXISTS status ENUM('Active', 'Inactive', 'Pending') DEFAULT 'Active';

-- Update all existing courses to Active status
UPDATE courses SET status = 'Active' WHERE status IS NULL;
# Personnel Management System - PCG CG-12

## Overview
The Personnel Management System has been completely redesigned and enhanced to provide better functionality for managing Coast Guard personnel data with proper categorization of Officers and Non-Officers.

## New Features

### ✅ Fixed Issues
- **Database Structure**: Updated personnel table to match expected schema
- **Proper Categorization**: Clear separation between Officers and Non-Officers
- **Enhanced UI**: Modern, responsive design with better user experience
- **Error Handling**: Improved error handling and user feedback

### ✅ New Features Added
- **Excel/CSV Upload**: Bulk upload personnel data via Excel or CSV files
- **Statistics Dashboard**: Real-time counts of Officers, Non-Officers, and Total Personnel
- **Delete Functionality**: Admin can delete individual personnel records
- **Responsive Design**: Mobile-friendly interface
- **Template Download**: CSV template for easy data preparation

## Database Structure

The personnel table now includes:
- `id` - Primary key
- `rank` - Military rank
- `lastname` - Last name
- `firstname` - First name
- `unit_code` - Unit identifier
- `category` - ENUM('Officer', 'Non-Officer')
- `remarks` - Additional notes
- `created_at` - Timestamp
- `updated_at` - Timestamp

## Files Created/Modified

### New Files:
1. `update_personnel_database.sql` - Database structure update
2. `update_personnel_database.php` - Database update script
3. `personnel_template.csv` - Sample CSV template
4. `personnel_upload_instructions.html` - Detailed upload instructions
5. `PERSONNEL_MANAGEMENT_README.md` - This documentation

### Modified Files:
1. `manage_personnel.php` - Completely redesigned with new features

## How to Use

### 1. Database Setup
Run the database update script:
```bash
php update_personnel_database.php
```

### 2. Access Personnel Management
Navigate to `manage_personnel.php` from the admin dashboard.

### 3. Upload Personnel Data
1. Prepare your data in Excel or CSV format
2. Use the provided template (`personnel_template.csv`) as reference
3. Upload file through the web interface
4. System will process and display results

### 4. File Format Requirements
CSV/Excel files must have these columns in order:
1. Rank
2. Last Name
3. First Name
4. Unit Code
5. Category (Officer/Non-Officer)
6. Remarks

## Features Breakdown

### 📊 Statistics Dashboard
- Real-time count of Officers
- Real-time count of Non-Officers
- Total personnel count
- Visual cards with gradient backgrounds

### 👔 Officers Section
- Dedicated table for Officer personnel
- Sortable by last name, first name
- Delete functionality for admins
- Professional styling

### 👷 Non-Officers Section
- Dedicated table for Non-Officer personnel
- Same features as Officers section
- Clear visual separation

### 📤 Excel/CSV Upload
- Supports .xlsx, .xls, and .csv formats
- Automatic duplicate detection and updates
- Error handling and success reporting
- Template download available

### 🗑️ Delete Functionality
- Admin-only delete buttons
- Confirmation dialogs
- Immediate feedback

## Technical Details

### Database Updates
- Unique constraint on (lastname, firstname, unit_code)
- Proper ENUM for category field
- Timestamp fields for tracking

### Security Features
- Admin-only access for sensitive operations
- Input validation and sanitization
- SQL injection prevention with prepared statements

### Error Handling
- Graceful handling of missing PhpSpreadsheet library
- CSV fallback for Excel files
- Detailed error messages
- Success/failure reporting

## Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile responsive design
- Progressive enhancement

## Dependencies
- PHP 7.0+
- MySQL 5.7+
- Optional: PhpSpreadsheet library for Excel support

## Troubleshooting

### Common Issues:
1. **Excel files not working**: Use CSV format or install PhpSpreadsheet
2. **Upload errors**: Check file format and column order
3. **Database errors**: Run the update script first
4. **Permission issues**: Ensure admin login

### Support:
- Check `personnel_upload_instructions.html` for detailed help
- Use the provided CSV template as reference
- Verify database connection in `db.php`

## Future Enhancements
- Bulk edit functionality
- Advanced search and filtering
- Export to Excel/PDF
- Personnel photo uploads
- Training history tracking
- Performance reports

---

**Last Updated**: December 2024
**Version**: 2.0
**Status**: Production Ready 
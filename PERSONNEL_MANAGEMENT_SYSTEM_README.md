# Personnel Training Management System
## Philippine Coast Guard CG-12

### 🎯 Project Overview

This comprehensive web application is designed for managing personnel assignments and training records in the Philippines Coast Guard CG-12. The system provides robust functionality for administrators to manage personnel data, conduct advanced searches, and perform bulk operations through file uploads.

### ✨ Key Features

#### 1. **Enhanced Reports & Search (reports_enhanced.html)**
- **Advanced Search Interface**: Multi-criteria search functionality
- **Real-time Filtering**: Search by name, rank, unit, category, training type, dates, and more
- **Responsive Design**: Mobile-friendly interface with accessibility features
- **Export Capabilities**: CSV export of search results
- **Professional UI**: Modern design with PCG branding

**Search Capabilities:**
- Personnel name (first, last, or full name)
- Rank and unit filtering
- Category-based filtering (Officer/Non-Officer)
- Training program search (CGOC, CGMC, CGNOC, etc.)
- Date range filtering
- Specialization and workshop search
- Serial number lookup

#### 2. **Personnel Management (manage_personnel.php)**
- **Role-Based Access Control**: Different permissions for admins and users
- **Add Personnel Functionality**: Comprehensive form for new personnel
- **Edit Capabilities**: Update existing personnel records
- **Delete Functions**: Remove personnel records (admin only)
- **File Management**: Upload and manage personnel documents
- **Statistics Dashboard**: Real-time counts and data visualization

**Admin Features:**
- ✅ Add new personnel with full data entry
- ✅ Edit all personnel fields
- ✅ Delete personnel records
- ✅ Bulk upload via CSV/Excel
- ✅ File document management

**User Features:**
- ✅ View all personnel data
- ✅ Edit rank and unit code only
- ❌ Cannot add or delete records

#### 3. **File Upload System**
- **CSV/Excel Support**: Both formats accepted for bulk operations
- **Data Validation**: Comprehensive validation and error reporting
- **Template System**: Pre-formatted templates for easy data entry
- **Duplicate Handling**: Smart handling of existing records
- **Error Reporting**: Detailed feedback on upload issues

#### 4. **Database Integration**
- **Comprehensive Schema**: Supports both Officer and Non-Officer personnel
- **Training Records**: Multiple training program fields
- **Career Tracking**: Promotion dates, service history, specializations
- **Document Storage**: File attachment support
- **Data Integrity**: Unique constraints and validation

### 🗄️ Database Schema

The system uses a comprehensive `personnel` table with the following structure:

```sql
CREATE TABLE personnel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Basic Information
    rank VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    firstname VARCHAR(100) NOT NULL,
    mi VARCHAR(10),
    serial_number VARCHAR(50) NOT NULL UNIQUE,
    unit_code VARCHAR(50) NOT NULL,
    sub_unit VARCHAR(100),
    category ENUM('Officer', 'Non-Officer') NOT NULL,
    
    -- Non-Officer Specific Fields
    cgmc_cgnoc_class VARCHAR(100),
    specialization VARCHAR(200),
    functional_course VARCHAR(200),
    blmc_almc_cgnoac VARCHAR(100),
    cgnosec VARCHAR(100),
    original_enlistment DATE,
    date_entered_service DATE,
    comp_ret VARCHAR(50),
    last_promotion_date DATE,
    
    -- Officer Specific Fields
    cgoc_class VARCHAR(100),
    cgscc_class VARCHAR(100),
    cgsc_class VARCHAR(100),
    cgec_class VARCHAR(100),
    third_level_career VARCHAR(100),
    
    -- Common Fields
    seminars_workshops TEXT,
    upload_file VARCHAR(255),
    remarks TEXT,
    
    -- System Fields
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 📁 File Structure

```
/workspace/
├── reports_enhanced.html              # Enhanced search and reporting interface
├── manage_personnel.php               # Main personnel management system
├── search_personnel_api.php           # API for advanced search functionality
├── personnel_upload_template_enhanced.csv  # Enhanced CSV template
├── personnel_upload_instructions_enhanced.html  # Comprehensive upload guide
├── db.php                            # Database connection
├── setup_database.sql               # Database schema setup
├── update_comprehensive_personnel_database.sql  # Schema updates
├── get_personnel_data.php           # API for individual personnel data
└── assets/
    ├── pcg_logo.png                 # Official PCG logo
    └── styles/                      # CSS styling files
```

### 🚀 Setup Instructions

#### 1. Database Setup
```sql
-- Run the database setup script
source setup_database.sql;

-- Apply comprehensive schema updates
source update_comprehensive_personnel_database.sql;
```

#### 2. Web Server Configuration
- Ensure PHP 7.4+ with MySQLi extension
- Configure file upload limits for CSV processing
- Set appropriate permissions for upload directories

#### 3. Initial Admin Setup
```php
// Create default admin user
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
```

### 🔧 Configuration

#### Database Connection (db.php)
```php
<?php
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "pcg_training";

$conn = new mysqli($servername, $username, $password, $dbname);
?>
```

#### File Upload Settings
- Maximum file size: 50MB
- Supported formats: CSV, XLS, XLSX
- Upload directory: `uploads/personnel/`

### 📊 Data Import Format

#### CSV Template Structure
The system supports comprehensive personnel data import with the following columns:

**Required Fields:**
- RANK, LASTNAME, FIRSTNAME, SERIAL_NUMBER, UNIT_CODE, CATEGORY

**Optional Fields:**
- MI, SUB_UNIT, training courses, dates, specializations, remarks

**Sample CSV Row:**
```csv
Lieutenant,Dela Cruz,Juan,A,CG-2023-001,CG-NCR,Operations,Officer,,,,,,,,,CGOC Class 2018-01,,,,,Advanced Training 2023,Excellent performance
```

### 🔐 Security Features

#### Authentication & Authorization
- Session-based authentication
- Role-based access control (Admin/User)
- Password hashing using PHP password_hash()
- SQL injection prevention via prepared statements

#### Data Validation
- Input sanitization and validation
- File type validation for uploads
- Serial number uniqueness enforcement
- Date format validation

#### Error Handling
- Comprehensive error logging
- User-friendly error messages
- Graceful failure handling
- Upload validation feedback

### 🎨 User Interface Features

#### Responsive Design
- Mobile-friendly layouts
- Touch-optimized controls
- Adaptive grid systems
- Cross-browser compatibility

#### Accessibility
- ARIA labels and descriptions
- Keyboard navigation support
- Screen reader compatibility
- High contrast design elements

#### User Experience
- Intuitive navigation
- Clear visual feedback
- Loading states and progress indicators
- Confirmation dialogs for destructive actions

### 🔍 Search & Reporting Capabilities

#### Advanced Search Features
- Multi-field search combinations
- Real-time search suggestions
- Search result highlighting
- Saved search functionality
- Export options (CSV, Print)

#### Reporting Options
- Personnel statistics dashboard
- Training completion reports
- Unit distribution analysis
- Career progression tracking
- Custom date range reports

### 🛠️ API Endpoints

#### Search Personnel API
**Endpoint:** `search_personnel_api.php`
**Method:** GET
**Parameters:**
- `name`: Search by personnel name
- `rank`: Filter by rank
- `unit`: Filter by unit code
- `category`: Filter by Officer/Non-Officer
- `training`: Search training programs
- `date_from`, `date_to`: Date range filtering

**Response:**
```json
{
    "success": true,
    "personnel": [...],
    "total_count": 150,
    "returned_count": 25
}
```

### 📈 Performance Considerations

#### Database Optimization
- Indexed fields for fast searching
- Optimized queries with prepared statements
- Pagination for large datasets
- Efficient join operations

#### File Upload Optimization
- Chunked file processing
- Memory-efficient CSV parsing
- Progress tracking for large uploads
- Error recovery mechanisms

### 🔄 Maintenance & Updates

#### Regular Tasks
- Database backup procedures
- Log file rotation
- Performance monitoring
- Security updates

#### Data Management
- Archive old records
- Validate data integrity
- Update training programs
- Manage file storage

### 🆘 Troubleshooting

#### Common Issues
1. **Upload Failures**: Check file format and size limits
2. **Search Performance**: Review database indexes
3. **Permission Errors**: Verify file system permissions
4. **Login Issues**: Check session configuration

#### Error Codes
- **ERR_001**: Database connection failure
- **ERR_002**: File upload size exceeded
- **ERR_003**: Invalid CSV format
- **ERR_004**: Duplicate serial number
- **ERR_005**: Authentication failure

### 📞 Support & Documentation

For technical support or feature requests:
- Review error logs in `/logs/` directory
- Check database connectivity
- Verify file permissions
- Consult API documentation

### 🔮 Future Enhancements

#### Planned Features
- Advanced reporting dashboard
- Email notification system
- Training schedule management
- Document version control
- API rate limiting
- Multi-language support

#### Technical Improvements
- Redis caching implementation
- Elasticsearch integration
- Real-time updates via WebSockets
- Enhanced security features
- Performance optimization

---

**Version:** 2.0
**Last Updated:** December 2024
**Developed for:** Philippine Coast Guard CG-12
**License:** Internal Use Only
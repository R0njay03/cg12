# Comprehensive Personnel Management System - PCG CG-12

## 🎯 Overview

The PCG CG-12 Training Management System now features a comprehensive personnel management system with detailed tracking for both Officers and Non-Officers, complete with role-based access control and file upload capabilities.

## 📊 Personnel Data Structure

### 🔧 Common Fields (Both Officers and Non-Officers)
- **RANK** - Military rank
- **Last Name** - Personnel's last name
- **First Name** - Personnel's first name
- **MI** - Middle Initial
- **Serial Number** - Unique personnel identifier
- **Unit Code** - Unit assignment code
- **Sub-Unit** - Specific unit subdivision
- **Category** - Officer or Non-Officer classification
- **Seminars/Workshops Attended** - Training and development activities
- **Upload File** - Document attachments (PDF, DOC, DOCX, JPG, JPEG, PNG)
- **Remarks** - Additional notes and comments

### 👷 Non-Officer Specific Fields
- **CGMC/CGNOC Class** - Coast Guard Master Chief/Non-Officer Course
- **Specialization** - Area of expertise/specialization
- **Functional Course** - Functional training completed
- **BLMC/ALMC/CGNOAC** - Basic/Advanced Leadership Management Course
- **CGNOSEC** - Coast Guard Non-Officer Senior Executive Course
- **Original Enlistment** - Date of original enlistment
- **Date Entered Service** - Service entry date
- **CompRet** - Component/Retirement status
- **Last Date of Promotion** - Most recent promotion date

### 👔 Officer Specific Fields
- **CGOC Class** - Coast Guard Officer Course
- **Coast Guard Station Commanders Course Class** - Command training
- **Coast Guard Staff Course** - Staff officer training
- **Coast Guard Officer Senior Executive Course** - Executive leadership training
- **Third Level Career Course** - Advanced career development

## 🔐 Role-Based Access Control

### 🔧 Administrator Access
- **Full Access**: View, edit, delete, and upload all personnel data
- **All Fields Editable**: Can modify any personnel information
- **File Management**: Upload and manage personnel documents
- **Data Management**: Create, update, and delete personnel records
- **System Administration**: Manage users and system settings

### 👤 Regular User Access
- **View-Only Access**: Can view all personnel data
- **Limited Editing**: Can only edit **Unit Code** and **Rank**
- **No File Upload**: Cannot upload documents
- **No Deletion**: Cannot delete personnel records
- **Read-Only**: All other fields are view-only

## 🚀 Key Features

### 📋 Comprehensive Data Management
- **Detailed Personnel Records**: Complete information tracking
- **Category-Specific Fields**: Different data for Officers vs Non-Officers
- **Training History**: Track all courses, seminars, and workshops
- **Career Progression**: Monitor promotions and career development
- **Document Management**: File upload and storage system

### 🎨 User Interface
- **Responsive Design**: Works on desktop and mobile devices
- **Modal Editing**: Clean, intuitive edit forms
- **Role Indicators**: Clear visual indicators of user permissions
- **Data Tables**: Comprehensive data display with sorting
- **File Preview**: Easy access to uploaded documents

### 🔒 Security Features
- **Session Management**: Secure user authentication
- **Role Verification**: Automatic permission checking
- **Input Validation**: Data integrity protection
- **SQL Injection Prevention**: Prepared statements
- **File Upload Security**: Restricted file types and validation

## 📁 Database Structure

```sql
CREATE TABLE personnel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Basic Information
    rank VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    firstname VARCHAR(100) NOT NULL,
    mi VARCHAR(10),
    serial_number VARCHAR(50) NOT NULL,
    unit_code VARCHAR(50) NOT NULL,
    sub_unit VARCHAR(100),
    category ENUM('Officer', 'Non-Officer') NOT NULL,
    
    -- Non-Officer Specific
    cgmc_cgnoc_class VARCHAR(100),
    specialization VARCHAR(200),
    functional_course VARCHAR(200),
    blmc_almc_cgnoac VARCHAR(100),
    cgnosec VARCHAR(100),
    original_enlistment DATE,
    date_entered_service DATE,
    comp_ret VARCHAR(50),
    last_promotion_date DATE,
    
    -- Officer Specific
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_personnel (serial_number)
);
```

## 🛠️ Technical Implementation

### Files and Components
1. **`manage_personnel.php`** - Main personnel management interface
2. **`get_personnel_data.php`** - AJAX data retrieval for editing
3. **`update_comprehensive_personnel_database.sql`** - Database structure
4. **`update_comprehensive_personnel_database.php`** - Database update script

### Key Technologies
- **PHP 7.0+** - Server-side processing
- **MySQL 5.7+** - Database management
- **JavaScript** - Client-side interactions
- **AJAX** - Asynchronous data loading
- **HTML5/CSS3** - Modern responsive interface

## 📊 Sample Data

### Non-Officer Example
```
Rank: Chief Petty Officer
Name: Miguel A. Torres
Serial: CG-2020-001
Unit: CG-SD (Station Davao)
Specialization: Operations
CGMC Class: CGMC Class 2020-01
Functional Course: Maritime Safety Administration
```

### Officer Example
```
Rank: Commander
Name: Roberto F. Fernandez
Serial: CG-2010-001
Unit: CG-HQ (Headquarters)
CGOC Class: CGOC Class 2010-01
CGSCC Class: CGSCC Class 2018-01
CGEC Class: CGEC Class 2022-01
```

## 🔧 How to Use

### For Administrators
1. **Login** with admin credentials
2. **View Personnel** - Browse all records with full details
3. **Edit Records** - Click "Edit" to modify any field
4. **Upload Files** - Click "📁" to attach documents
5. **Delete Records** - Remove personnel entries when needed

### For Regular Users
1. **Login** with user credentials
2. **View Personnel** - Browse all records (view-only)
3. **Limited Editing** - Can only edit Unit Code and Rank
4. **No File Upload** - Cannot upload documents
5. **No Deletion** - Cannot delete records

## 📈 Benefits

### For Organization
- **Comprehensive Tracking**: Complete personnel information management
- **Training Monitoring**: Track all training and development activities
- **Career Development**: Monitor promotions and career progression
- **Document Management**: Centralized file storage and access
- **Data Integrity**: Secure, validated data entry and storage

### For Users
- **Easy Access**: Intuitive interface for data management
- **Role-Based Security**: Appropriate access levels for different users
- **Mobile Friendly**: Access from any device
- **Real-Time Updates**: Immediate data synchronization
- **File Management**: Easy document upload and retrieval

## 🔮 Future Enhancements

### Planned Features
- **Advanced Search**: Filter and search capabilities
- **Reporting**: Generate personnel reports and statistics
- **Bulk Operations**: Mass update and import features
- **Audit Trail**: Track all changes and modifications
- **Integration**: Connect with other training systems

### Technical Improvements
- **API Development**: RESTful API for external integrations
- **Performance Optimization**: Database indexing and caching
- **Enhanced Security**: Multi-factor authentication
- **Backup Systems**: Automated data backup and recovery
- **Mobile App**: Native mobile application

## 📞 Support and Maintenance

### System Requirements
- **Web Server**: Apache/Nginx with PHP support
- **Database**: MySQL 5.7 or higher
- **PHP Version**: 7.0 or higher
- **File Permissions**: Write access for uploads directory

### Maintenance Tasks
- **Regular Backups**: Database and file system backups
- **Security Updates**: Keep PHP and MySQL updated
- **Performance Monitoring**: Monitor system performance
- **User Management**: Regular user account maintenance
- **Data Validation**: Periodic data integrity checks

---

**Last Updated**: December 2024
**Version**: 3.0 (Comprehensive)
**Status**: Production Ready
**Security Level**: High 
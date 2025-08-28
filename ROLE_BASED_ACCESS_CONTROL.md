# Role-Based Access Control (RBAC) System

## Overview
The PCG CG-12 Training Management System now implements a comprehensive Role-Based Access Control (RBAC) system that provides different levels of access based on user roles. This ensures data security and proper access management.

## User Roles

### 🔧 Administrator (admin)
**Full Access Level**
- **Username**: admin
- **Password**: admin123
- **Permissions**:
  - ✅ View all personnel data
  - ✅ Edit personnel records
  - ✅ Delete personnel records
  - ✅ Upload Excel/CSV files
  - ✅ Manage courses
  - ✅ Access all system features
  - ✅ Create new users
  - ✅ View and generate reports

### 👤 Regular User (user)
**View-Only Access Level**
- **Username**: testuser (created by script)
- **Password**: test123
- **Permissions**:
  - ✅ View all personnel data
  - ✅ Search and filter personnel
  - ✅ View course catalog
  - ✅ View reports (read-only)
  - ❌ Cannot edit personnel records
  - ❌ Cannot delete personnel records
  - ❌ Cannot upload files
  - ❌ Cannot manage courses
  - ❌ Cannot create users

## System Architecture

### Authentication Flow
1. User logs in via `login.html`
2. Credentials verified against `users` table
3. Session created with user role
4. Redirected to appropriate dashboard based on role:
   - **Admin**: `admin-dashboard-enhanced.php`
   - **User**: `user-dashboard.html`

### Access Control Implementation
- **Session-based authentication**
- **Role verification on each page**
- **Automatic redirect for unauthorized access**
- **Visual indicators of user role and permissions**

## Files and Components

### Core Files
1. **`login.php`** - Authentication and role-based redirection
2. **`manage_personnel.php`** - Role-based personnel management
3. **`user-dashboard.html`** - Regular user dashboard
4. **`add_regular_user.php`** - Create new regular users
5. **`create_test_user.php`** - Create test user for testing

### Database Structure
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## How to Use

### For Administrators

#### 1. Create Regular Users
```bash
# Access the user creation page
http://localhost/cg12/add_regular_user.php
```

#### 2. Manage Personnel
- Full access to all personnel management features
- Can upload Excel/CSV files
- Can edit and delete records
- Can view all statistics

#### 3. System Administration
- Access to admin dashboard
- User management capabilities
- System configuration

### For Regular Users

#### 1. Login
- Use credentials provided by administrator
- Automatically redirected to user dashboard

#### 2. View Personnel Data
- Browse all personnel records
- Search and filter functionality
- View-only access (no editing)

#### 3. Access Reports
- View training reports
- Read-only access to statistics

## Security Features

### Authentication Security
- **Password Hashing**: All passwords stored using PHP's `password_hash()`
- **Session Management**: Secure session handling
- **SQL Injection Prevention**: Prepared statements used throughout
- **Input Validation**: All user inputs validated and sanitized

### Access Control Security
- **Role Verification**: Every page checks user role
- **Unauthorized Access Prevention**: Automatic redirects
- **Session Timeout**: Sessions expire after inactivity
- **CSRF Protection**: Form tokens for sensitive operations

### Data Protection
- **XSS Prevention**: Output escaping on all user data
- **File Upload Security**: Restricted file types and validation
- **Database Security**: Proper connection handling and error management

## Testing the System

### 1. Create Test User
```bash
# Run the test user creation script
http://localhost/cg12/create_test_user.php
```

### 2. Test Admin Access
- Login with: admin / admin123
- Verify full access to all features
- Test personnel management functions

### 3. Test Regular User Access
- Login with: testuser / test123
- Verify view-only access
- Confirm no editing capabilities

### 4. Test Security
- Try accessing admin features as regular user
- Verify proper redirects and access denied messages
- Test session management

## User Interface Features

### Admin Interface
- **Green indicator**: Shows admin status
- **Full functionality**: All buttons and features visible
- **Upload section**: Excel/CSV upload available
- **Delete buttons**: Available for all records

### Regular User Interface
- **Yellow indicator**: Shows regular user status
- **View-only notice**: Clear explanation of limitations
- **No edit buttons**: Delete and edit functions hidden
- **Permission display**: Shows allowed/denied actions

## Error Handling

### Authentication Errors
- Invalid credentials
- Session expiration
- Unauthorized access attempts

### Access Control Errors
- Role verification failures
- Permission denied messages
- Automatic redirects to login

### User Feedback
- Clear error messages
- Success confirmations
- Permission explanations

## Maintenance and Administration

### User Management
- Create new regular users via `add_regular_user.php`
- Monitor user activity through logs
- Manage user roles and permissions

### System Monitoring
- Track login attempts
- Monitor access patterns
- Review security logs

### Backup and Recovery
- Regular database backups
- User data protection
- System recovery procedures

## Troubleshooting

### Common Issues

#### 1. User Cannot Login
- Verify username and password
- Check database connection
- Ensure user exists in database

#### 2. Access Denied Errors
- Verify user role in database
- Check session status
- Clear browser cache and cookies

#### 3. Permission Issues
- Confirm user role assignment
- Check role-based access logic
- Verify page access controls

### Support Procedures
1. Check user credentials
2. Verify database connectivity
3. Review error logs
4. Test with different user roles
5. Contact system administrator

## Future Enhancements

### Planned Features
- **Multi-factor Authentication**: SMS/email verification
- **Role Hierarchy**: Multiple admin levels
- **Audit Logging**: Detailed access tracking
- **Permission Groups**: Custom permission sets
- **API Access**: Secure API authentication

### Security Improvements
- **Password Policies**: Enforce strong passwords
- **Account Lockout**: Prevent brute force attacks
- **Session Encryption**: Enhanced session security
- **Real-time Monitoring**: Live security monitoring

---

**Last Updated**: December 2024
**Version**: 1.0
**Status**: Production Ready
**Security Level**: High 
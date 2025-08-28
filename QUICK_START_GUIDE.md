# Quick Start Guide - PCG CG-12 Personnel Management System

## ✅ System Status: FIXED AND READY

The database structure issue has been resolved. The personnel management system is now fully functional with role-based access control.

## 🔧 Database Fix Applied

- **Issue**: Missing `lastname` column in personnel table
- **Solution**: Table structure updated with correct columns
- **Result**: 10 sample personnel records inserted
- **Status**: ✅ RESOLVED

## 👥 User Accounts Available

### Administrator Account
- **Username**: `admin`
- **Password**: `admin123`
- **Role**: Administrator
- **Access**: Full access (view, edit, delete, upload)

### Regular User Account
- **Username**: `testuser`
- **Password**: `test123`
- **Role**: Regular User
- **Access**: View-only access

## 🚀 How to Test the System

### Step 1: Test Administrator Access
1. Go to: `http://localhost/cg12/login.html`
2. Login with: `admin` / `admin123`
3. You should be redirected to the admin dashboard
4. Click "Manage Personnel" to access full functionality
5. Verify you can:
   - View all personnel data
   - See upload Excel/CSV section
   - See delete buttons for each record
   - See green admin indicator

### Step 2: Test Regular User Access
1. Go to: `http://localhost/cg12/login.html`
2. Login with: `testuser` / `test123`
3. You should be redirected to the user dashboard
4. Click "View Personnel Data" to access view-only functionality
5. Verify you can:
   - View all personnel data
   - See yellow user indicator
   - See view-only notice
   - NO upload section visible
   - NO delete buttons visible

### Step 3: Test Role-Based Features
1. **As Admin**: Try uploading the provided CSV template
2. **As User**: Try accessing admin-only features (should be blocked)
3. **As Admin**: Try deleting a personnel record
4. **As User**: Verify you cannot see delete buttons

## 📊 Expected Results

### Administrator Interface
- ✅ Green indicator: "🔧 Administrator"
- ✅ Full functionality with all buttons
- ✅ Excel/CSV upload section
- ✅ Delete buttons for each record
- ✅ Statistics dashboard

### Regular User Interface
- ✅ Yellow indicator: "👁️ Regular User"
- ✅ View-only notice explaining limitations
- ✅ No edit/delete buttons
- ✅ No upload section
- ✅ Permission display showing restrictions

## 🔧 Troubleshooting

### If you get database errors:
1. Run: `http://localhost/cg12/fix_personnel_table.php`
2. This will fix any database structure issues

### If you can't login:
1. Check that XAMPP is running
2. Verify database connection in `db.php`
3. Try creating a new test user: `http://localhost/cg12/create_test_user.php`

### If role-based access isn't working:
1. Clear browser cache and cookies
2. Logout and login again
3. Check that sessions are working properly

## 📁 Key Files

- `manage_personnel.php` - Main personnel management page
- `login.php` - Authentication and role-based redirection
- `user-dashboard.html` - Regular user dashboard
- `fix_personnel_table.php` - Database fix script
- `create_test_user.php` - Create test user script

## 🎯 Success Criteria

The system is working correctly if:

1. ✅ Admin can access all features
2. ✅ Regular user can only view data
3. ✅ No database errors occur
4. ✅ Role indicators display correctly
5. ✅ Upload and delete functions work for admin only
6. ✅ View-only restrictions work for regular users

## 📞 Support

If you encounter any issues:
1. Check the error messages
2. Run the database fix script if needed
3. Verify user credentials
4. Check XAMPP and MySQL status

---

**Status**: ✅ SYSTEM READY FOR TESTING
**Last Updated**: December 2024
**Version**: 2.0 (Fixed) 
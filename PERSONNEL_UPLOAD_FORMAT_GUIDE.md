# Personnel Upload Format Guide - PCG CG-12

## 📋 File Format Requirements

### **Supported File Types:**
- **CSV (.csv)** - Comma-separated values
- **Excel (.xlsx)** - Microsoft Excel 2007+
- **Excel (.xls)** - Microsoft Excel 97-2003

### **Required Column Structure (25 columns):**

| Column | Field Name | Required | Description |
|--------|------------|----------|-------------|
| 1 | RANK | ✅ | Military rank (e.g., Commander, Lieutenant, Chief Petty Officer) |
| 2 | Last Name | ✅ | Personnel's last name |
| 3 | First Name | ✅ | Personnel's first name |
| 4 | MI | ❌ | Middle initial |
| 5 | Serial Number | ✅ | Unique personnel identifier (e.g., CG-2020-001) |
| 6 | Unit Code | ✅ | Unit assignment code (e.g., CG-HQ, CG-SD) |
| 7 | Sub-Unit | ❌ | Specific unit subdivision |
| 8 | Category | ✅ | **Non-Officer**, **General Line Officer**, or **Technical Officer** |
| 9 | CGMC/CGNOC Class | ❌ | Non-Officer training class |
| 10 | SPECIALIZATION | ❌ | Area of expertise/specialization |
| 11 | FUNCTIONAL | ❌ | Functional training completed |
| 12 | BLMC/ALMC/CGNOAC | ❌ | Leadership management course |
| 13 | CGNOSEC | ❌ | Non-Officer senior executive course |
| 14 | Original Enlistment | ❌ | Date of original enlistment (YYYY-MM-DD) |
| 15 | Date Entered the Service | ❌ | Service entry date (YYYY-MM-DD) |
| 16 | CompRet | ❌ | Component/Retirement status |
| 17 | Last Date of Promotion | ❌ | Most recent promotion date (YYYY-MM-DD) |
| 18 | CGOC Class | ❌ | Officer course class |
| 19 | Coast Guard Station Commanders Course Class | ❌ | Command training class |
| 20 | Coast Guard Staff Course | ❌ | Staff officer training |
| 21 | Coast Guard Officer Senior Executive Course | ❌ | Executive leadership training |
| 22 | Third Level Career Course | ❌ | Advanced career development |
| 23 | Seminar/Workshops | ❌ | Training and development activities |
| 24 | Upload File | ❌ | File attachment path |
| 25 | Remarks | ❌ | Additional notes and comments |

## 👔 Officer Categories

### **General Line Officers:**
- **Description:** Traditional command and leadership officers
- **Training Focus:** Command, leadership, operations management
- **Key Fields:** CGOC Class, CGSCC Class, CGSC Class, CGEC Class
- **Example Ranks:** Commander, Lieutenant, Lieutenant Junior Grade

### **Technical Officers:**
- **Description:** Specialized technical and engineering officers
- **Training Focus:** Technical expertise, engineering, specialized operations
- **Key Fields:** CGOC Class, Technical training, specialized courses
- **Example Ranks:** Lieutenant (Technical), Engineering Officers

## 👷 Non-Officer Personnel

### **Training Programs:**
- **CGMC/CGNOC Class:** Coast Guard Master Chief/Non-Officer Course
- **SPECIALIZATION:** Area of expertise (e.g., Operations, Machinery, Communications)
- **FUNCTIONAL:** Functional training courses
- **BLMC/ALMC/CGNOAC:** Basic/Advanced Leadership Management Course
- **CGNOSEC:** Coast Guard Non-Officer Senior Executive Course

### **Career Tracking:**
- **Original Enlistment:** Date of first enlistment
- **Date Entered Service:** Service entry date
- **CompRet:** Component/Retirement status
- **Last Date of Promotion:** Most recent promotion

## 📊 Sample Data

### **General Line Officer Example:**
```csv
Commander,Fernandez,Roberto,F,CG-2010-001,CG-HQ,Operations Directorate,General Line Officer,,,,,,,,,CGOC Class 2010-01,CGSCC Class 2018-01,CGSC Class 2015-02,CGEC Class 2022-01,Third Level Career Course 2020,Strategic Leadership Seminar 2023; International Maritime Law Conference 2022; Command and Control Workshop 2021,,Senior officer with extensive command experience and strategic planning expertise
```

### **Technical Officer Example:**
```csv
Lieutenant Junior Grade,Villanueva,Patricia,I,CG-2019-004,CG-DM,Technical Operations,Technical Officer,,,,,,,,,CGOC Class 2019-01,,,,,Technical Operations Management 2023; Engineering Systems Workshop 2022; Technical Planning Seminar 2021,,Technical officer with strong engineering background and operational expertise
```

### **Non-Officer Example:**
```csv
Chief Petty Officer,Torres,Miguel,A,CG-2020-001,CG-SD,Operations Division,Non-Officer,CGMC Class 2020-01,Operations Specialization,Maritime Safety Administration,BLMC Class 2019-02,CGNOSEC Class 2021-01,2015-03-15,2015-03-15,Active,2023-06-15,,,,,,Maritime Security Workshop 2023; Search and Rescue Training 2022; Environmental Protection Seminar 2021,,Experienced operations specialist with strong leadership skills
```

## 🔧 Upload Process

### **Step-by-Step Instructions:**

1. **Prepare Your File:**
   - Use the provided template: `personnel_upload_template.csv`
   - Ensure all required fields are filled
   - Use semicolons (;) to separate multiple seminars/workshops

2. **Access Upload Feature:**
   - Login as Administrator
   - Navigate to Personnel Management
   - Locate the upload section

3. **Upload File:**
   - Click "Choose File"
   - Select your CSV/Excel file
   - Click "Upload & Update"

4. **Review Results:**
   - Check success/error messages
   - Verify data in the personnel tables
   - Review any error reports

## ⚠️ Important Notes

### **Data Validation:**
- **Serial Number:** Must be unique across all personnel
- **Category:** Must be exactly "Non-Officer", "General Line Officer", or "Technical Officer"
- **Dates:** Use YYYY-MM-DD format
- **Required Fields:** Cannot be empty

### **File Requirements:**
- **Header Row:** Must be included (first row)
- **Encoding:** UTF-8 recommended
- **Delimiter:** Comma for CSV files
- **File Size:** Maximum 10MB

### **Error Handling:**
- **Duplicate Records:** Will update existing records based on Serial Number
- **Invalid Data:** Will be skipped with error reporting
- **Missing Fields:** Will use default values where appropriate

## 📞 Support

For technical support or questions about the upload format:
- **Email:** cg12-support@pcg.gov.ph
- **Phone:** +63 2 1234 5678
- **Documentation:** See `personnel_upload_instructions.html`

---

**Last Updated:** December 2024
**Version:** 2.0 (Enhanced Officer Categories)
**Status:** Production Ready 
# Attendance Management System

A comprehensive web-based attendance tracking system built with PHP, HTML, CSS, and MySQL.

## Features

### Teacher Portal
- 🔐 Secure login system
- 📊 Dashboard with statistics
- ✏️ Mark attendance for students by class and date
- 📚 Manage multiple classes

### Student Portal
- 🔐 Secure login system
- 📈 View attendance percentage
- 📋 View detailed attendance records
- 📊 Visual statistics and progress bars

## Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server (XAMPP recommended)

### Installation

1. **Clone or download this project** to your web server directory
   ```
   C:\xampp\htdocs\Attendence_management_system
   ```

2. **Start your web server**
   - Open XAMPP Control Panel
   - Start Apache and MySQL

3. **Import the database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Click "Import" tab
   - Select `setup_database.sql` from the project folder
   - Click "Go"

4. **Configure database connection** (if needed)
   - Open `config.php`
   - Update database credentials if your MySQL settings differ:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');  // Your MySQL password
     define('DB_NAME', 'attendance_db');
     ```

5. **Access the application**
   - Open browser: `http://localhost/Attendence_management_system/`

## Test Credentials

### Teacher Login
- **Username:** teacher1
- **Password:** teacher123

### Student Login
- **Username:** student1
- **Password:** student123

(Additional student accounts: student2, student3, student4 - all with password: student123)

## Project Structure

```
Attendence_management_system/
├── setup_database.sql      # Database schema with sample data
├── config.php              # Database configuration
├── session.php             # Session management
├── index.php               # Landing page
├── teacher_login.php       # Teacher authentication
├── teacher_dashboard.php   # Teacher dashboard
├── mark_attendance.php     # Attendance marking interface
├── student_login.php       # Student authentication
├── student_dashboard.php   # Student dashboard
├── logout.php              # Logout handler
└── styles.css              # Complete styling
```

## Database Schema

- **users** - Stores teacher and student login credentials
- **classes** - Stores class/subject information
- **students** - Links students to classes with roll numbers
- **attendance** - Stores daily attendance records

## Usage

### For Teachers

1. Login with teacher credentials
2. View your assigned classes on the dashboard
3. Click "Mark Attendance" for any class
4. Select the date (today or past dates)
5. Mark each student as Present or Absent
6. Click "Submit Attendance"

### For Students

1. Login with student credentials
2. View your attendance statistics:
   - Overall percentage
   - Days present/absent
   - Total days recorded
3. Review recent attendance records in the table

## Security Features

- ✅ Password hashing using bcrypt
- ✅ Prepared statements (SQL injection prevention)
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ Input validation and sanitization

## Technologies Used

- **Backend:** PHP 7.4+
- **Database:** MySQL
- **Frontend:** HTML5, CSS3
- **Design:** Responsive layouts with gradient themes

## Browser Support

- ✅ Chrome (recommended)
- ✅ Firefox
- ✅ Edge
- ✅ Safari

## Troubleshooting

**"Connection failed" error:**
- Ensure MySQL is running in XAMPP
- Verify database credentials in `config.php`
- Check that `attendance_db` database exists

**Login not working:**
- Verify the database was imported successfully
- Check that sample users exist in the `users` table
- Clear browser cache and cookies

**Page styling broken:**
- Verify `styles.css` is in the project root
- Check browser console for errors
- Clear browser cache

## Support

For issues or questions, refer to the `walkthrough.md` file for detailed documentation.

## License

This project is created for educational purposes.

---

**Created as an attendance management solution for educational institutions.**

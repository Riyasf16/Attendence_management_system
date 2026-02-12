# Karpagam College Attendance Management System

A web-based attendance management system designed for the BSc Computer Science Department of Karpagam College of Arts and Science.

## 🚀 Quick Start Guide

### Prerequisites
- **XAMPP** or **WAMP** Server (Requires PHP & MySQL)
- A web browser (Chrome, Firefox, or Edge)

### Installation Steps

1.  **Start XAMPP**:
    - Open XAMPP Control Panel.
    - Start **Apache** and **MySQL**.

2.  **Setup the Database**:
    - Open your browser and go to `http://localhost/phpmyadmin`.
    - Create a new database named `attendance_db`.
    - Import the files in this order:
        1.  `attendance_db (1).sql` (Schema)
        2.  `complete_full_attendance.sql` (If available, for sample data)
    - *Alternatively*, just import `complete_full_attendance.sql` if it contains the full schema.

3.  **Deploy the Files**:
    - Copy the `Attendence_management_system` folder to your XAMPP `htdocs` directory.
    - Path: `C:\xampp\htdocs\Attendence_management_system`

4.  **Run the Application**:
    - Open your browser and visit:
      `http://localhost/Attendence_management_system/`

---

## 🔐 Login Credentials

### Faculty Portal
- **Username**: `teacher_fine`
- **Password**: `teacher123`

### Student Portal
- **Username**: `student1`
- **Password**: `student123`
- *(Or use any student roll number with password `student123`)*

---

## 🛠 Features (v2.0 Redesign)
- **Karpagam Identity**: Official college branding and color theme (`#003366`).
- **Teacher Dashboard**: View timetable, mark attendance, and track student stats.
- **Student Dashboard**: Check attendance percentage, view history (scrollable table), and eligibility status.
- **Responsive Design**: Professional UI suitable for lab computers.

---
**Developed for BSc Computer Science Department**
© 2026 Karpagam College of Arts and Science

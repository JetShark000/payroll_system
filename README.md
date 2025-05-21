# Payroll System

Author: JetShark000

## Overview

This is a web-based Payroll System built with PHP, MySQL and CSS using bootstrap, designed to help organisations manage employee payroll, generate payslips, and handle payroll records securely. The system supports both admin and employee roles, with features for payroll calculation, payslip generation (PDF), and user management.

---

## Features

- **Admin Panel**
  - Create, edit, and delete users (employees and admins)
  - Create, edit, and delete payroll records for employees
  - Generate and download payslips as PDFs
  - Search and filter payroll records
  - Secure storage of sensitive data (e.g., hashed NI numbers)

- **Employee Panel**
  - View monthly payslips
  - Download payslips as PDFs

- **Security**
  - Passwords and NI numbers are hashed before storage
  - Session-based authentication and role-based access

---

## Database Schema

### `users` Table

| Column    | Type         | Description                |
|-----------|--------------|----------------------------|
| id        | INT (PK)     | User ID                    |
| name      | VARCHAR(100) | Full name                  |
| password  | VARCHAR(255) | Hashed password            |
| role      | VARCHAR(20)  | 'employee' or 'admin'      |
| ni_no     | VARCHAR(255) | Hashed NI number           |

### `payroll` Table

| Column         | Type           | Description                        |
|----------------|----------------|------------------------------------|
| id             | INT (PK)       | Payroll record ID                  |
| user_id        | INT (FK)       | Reference to users.id              |
| name           | VARCHAR(100)   | Employee name                      |
| monthly_salary | DECIMAL(10,2)  | Monthly salary                     |
| ni_category    | VARCHAR(2)     | NI category                        |
| income_tax     | DECIMAL(10,2)  | Income tax                         |
| employee_ni    | DECIMAL(10,2)  | Employee NI                        |
| employer_ni    | DECIMAL(10,2)  | Employer NI                        |
| role           | VARCHAR(50)    | Employee role                      |
| overtime       | DECIMAL(10,2)  | Overtime pay                       |
| deductions     | DECIMAL(10,2)  | Deductions                         |
| month          | VARCHAR(7)     | Payroll month (YYYY-MM)            |
| ni_no          | VARCHAR(255)   | Hashed NI number                   |

### `salaries` Table (for legacy/simple payslip storage)

| Column   | Type           | Description                |
|----------|----------------|----------------------------|
| id       | INT (PK)       | Record ID                  |
| user_id  | INT (FK)       | Reference to users.id      |
| month    | VARCHAR(7)     | Month (YYYY-MM)            |
| amount   | DECIMAL(10,2)  | Amount paid                |
| note     | TEXT           | Optional note              |

---

## Setup Instructions (XAMPP)

1. **Clone the Repository**
   ```sh
   git clone https://github.com/JetShark000/payroll_system.git

```markdown
# Payroll System

Author: JetShark000

## Overview

This is a web-based Payroll System built with HTML,PHP, MySQL and Bootstrap. It is designed to help organisations manage employee payroll, generate payslips, and handle payroll records securely. The system supports both admin and employee roles, with features for payroll calculation, payslip generation (PDF), and user management.

---

## Features

- **Admin Panel**
  - Create, edit, and delete users (employees and admins)
  - Create, edit, and delete payroll records for employees
  - Generate and download payslips as PDFs
  - Search and filter payroll records
  - Secure storage of sensitive data (e.g., hashed NI numbers)

- **Employee Panel**
  - View monthly payslips
  - Download payslips as PDFs

- **Security**
  - Passwords and NI numbers are hashed before storage
  - Session-based authentication and role-based access

---

## Database Schema

### `users` Table

| Column    | Type         | Description                |
|-----------|--------------|----------------------------|
| id        | INT (PK)     | User ID                    |
| name      | VARCHAR(100) | Full name                  |
| password  | VARCHAR(255) | Hashed password            |
| role      | VARCHAR(20)  | 'employee' or 'admin'      |
| ni_no     | VARCHAR(255) | Hashed NI number           |

### `payroll` Table

| Column         | Type           | Description                        |
|----------------|----------------|------------------------------------|
| id             | INT (PK)       | Payroll record ID                  |
| user_id        | INT (FK)       | Reference to users.id              |
| name           | VARCHAR(100)   | Employee name                      |
| monthly_salary | DECIMAL(10,2)  | Monthly salary                     |
| ni_category    | VARCHAR(2)     | NI category                        |
| income_tax     | DECIMAL(10,2)  | Income tax                         |
| employee_ni    | DECIMAL(10,2)  | Employee NI                        |
| employer_ni    | DECIMAL(10,2)  | Employer NI                        |
| role           | VARCHAR(50)    | Employee role                      |
| overtime       | DECIMAL(10,2)  | Overtime pay                       |
| deductions     | DECIMAL(10,2)  | Deductions                         |
| month          | VARCHAR(7)     | Payroll month (YYYY-MM)            |
| ni_no          | VARCHAR(255)   | Hashed NI number                   |

### `salaries` Table (for legacy/simple payslip storage)

| Column   | Type           | Description                |
|----------|----------------|----------------------------|
| id       | INT (PK)       | Record ID                  |
| user_id  | INT (FK)       | Reference to users.id      |
| month    | VARCHAR(7)     | Month (YYYY-MM)            |
| amount   | DECIMAL(10,2)  | Amount paid                |
| note     | TEXT           | Optional note              |

---

## Setup Instructions (XAMPP)

1. **Clone the Repository**
   ```sh
   git clone https://github.com/JetShark000/payroll_system.git
   ```

2. **Move the Project**
   - Place the project folder in `c:\xampp\htdocs\payroll_system`

3. **Create the Database**
   - Start XAMPP and ensure MySQL and Apache are running.
   - Open phpMyAdmin at [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Create a database named `payroll_db`
   - Import the following schema:

   ```sql
   CREATE TABLE users (
     id INT PRIMARY KEY,
     name VARCHAR(100) NOT NULL,
     password VARCHAR(255) NOT NULL,
     role VARCHAR(20) NOT NULL,
     ni_no VARCHAR(255) NOT NULL
   );

   CREATE TABLE payroll (
     id INT AUTO_INCREMENT PRIMARY KEY,
     user_id INT NOT NULL,
     name VARCHAR(100) NOT NULL,
     monthly_salary DECIMAL(10,2) NOT NULL,
     ni_category VARCHAR(2) NOT NULL,
     income_tax DECIMAL(10,2) NOT NULL,
     employee_ni DECIMAL(10,2) NOT NULL,
     employer_ni DECIMAL(10,2) NOT NULL,
     role VARCHAR(50) NOT NULL,
     overtime DECIMAL(10,2) DEFAULT 0,
     deductions DECIMAL(10,2) DEFAULT 0,
     month VARCHAR(7) NOT NULL,
     ni_no VARCHAR(255) NOT NULL,
     FOREIGN KEY (user_id) REFERENCES users(id)
   );

   CREATE TABLE salaries (
     id INT AUTO_INCREMENT PRIMARY KEY,
     user_id INT NOT NULL,
     month VARCHAR(7) NOT NULL,
     amount DECIMAL(10,2) NOT NULL,
     note TEXT,
     FOREIGN KEY (user_id) REFERENCES users(id)
   );
   ```

4. **Configure Database Connection**
   - Edit `db.php` if your MySQL username or password is different from the default.

5. **Access the Application**
   - Go to [http://localhost/payroll_system/login.php](http://localhost/payroll_system/login.php) in your browser.

---

## Future Plans

- **SaaS Conversion:**  
  Refactor the system for multi-tenant support, allowing multiple companies to use the same platform with isolated data.

- **UI/UX Improvements:**  
  Rebuild the frontend using React for a modern, responsive, and interactive user experience.

- **API Development:**  
  Expose RESTful APIs for integration with other HR and accounting systems.

- **Advanced Security:**  
  Add two-factor authentication, audit logs, and encryption for sensitive fields.

- **Payroll Automation:**  
  Add features for automated payroll runs, email notifications, and direct deposit integration.

---

## License

This project is for educational and demonstration purposes by me, JetShark000.  
See the file citations for any 3rd party code references.

---
```

# YU Lost & Found — SWE 322 Advanced Web Programming Project

A web application where university students report **lost** and **found** belongings,
browse reports, send claims to the person who posted an item, and manage their own reports.

**Technologies:** HTML5 · CSS · JavaScript · PHP · MySQL (XAMPP)

---

## 1. How to run it (XAMPP)

1. Install and open **XAMPP**. Start **Apache** and **MySQL**.
2. Copy this whole folder to `C:\xampp\htdocs\project322\`.
3. Open **http://localhost/project322/sql/setup.php** once.
   It creates the database `yu_lost_found`, the 4 tables and the sample data.
   *(Alternative: import `sql/database.sql` in phpMyAdmin.)*
4. Open **http://localhost/project322/html/index.php**.

### Demo accounts

| Role | Username | Password |
|------|----------|----------|
| Administrator | `admin` | `Admin@1234` |
| Student | `sara_ali` | `Student@123` |
| Student | `omar_k` | `Student@123` |

### Database login

By default the code uses XAMPP's defaults (`root`, empty password, database `yu_lost_found`).
To change them, copy `sql/db_login.example.php` to `C:/project/db_login.php`
(or to `sql/db_login.php`) and edit the values. That file is ignored by git.

> **Old database from an earlier version?** Open `sql/setup.php?reset=1` once
> (works from localhost only) — it deletes the old database and builds the new one.

---

## 2. Project structure

```
html/                      the website (pages are .php because they read the database)
  about.html               static HTML5 page: how it works + FAQ
  index.php                home page - statistics + latest reports
  browse.php               search and filter all reports
  item-details.php         one report, photo, contact, claim form
  report-lost.php          form: report a lost item
  report-found.php         form: report a found item
  edit-report.php          form: edit an existing report
  login.php                sign in + create account
  dashboard.php            my reports, claims on my items, my claims
  students.php             administrator: manage student accounts
  categories.php           administrator: manage item categories
  inc/                     shared pieces: header, footer, report form, item card
  css/style.css            all styling (responsive)
js/index.js                JavaScript: menu, validation, confirm dialogs, image preview
sql/                       server side (PHP + MySQL)
  config.php               database connection + helper functions (used by everything)
  setup.php                one-click install (database + tables + sample data)
  db_creation.php          step 1: create the database
  tableCreat.php           step 2: create the tables
  seed_data.php            step 3: insert the sample rows
  database.sql             importable copy of the finished database
  regis_action.php         create an account
  student_login.php        log in          logout.php   log out
  report_action.php        create a report
  update_report.php        update a report
  report_status.php        mark a report returned / re-open it
  delete_report.php        delete a report
  claim_action.php         create / approve / reject / withdraw claims
  student_action.php       administrator: create / update / delete students
  category_action.php      administrator: create / update / delete categories
  image.php                sends a report's photo to the browser
```

---

## 3. Database (MySQL)

Four tables (requirement: at least 3), each with **5 or more rows** of sample data:

| Table | Purpose | Sample rows |
|-------|---------|-------------|
| `Student` | accounts (id, username, hashed password, email, role) | 6 |
| `Category` | item categories (Electronics, Books & Notes, ...) | 6 |
| `ItemReports` | lost/found reports; links to `Student` and `Category` | 8 |
| `Claims` | messages/claims on a report; links to `ItemReports` and `Student` | 5 |

Relationships (foreign keys): `ItemReports.user_id → Student`, `ItemReports.category_id → Category`,
`Claims.ReportID → ItemReports`, `Claims.user_id → Student`.
Deleting a student or a report also deletes the rows that depend on it (`ON DELETE CASCADE`).

### Names in the team plan vs. the code

| Plan | Table in the code | Main columns |
|------|-------------------|--------------|
| users | `Student` | user_id, user_name, email, password, role |
| items | `ItemReports` | ReportID, Title, Description, Photo (image), Status (lost/found), ReportState (open/returned), user_id, category_id |
| categories | `Category` | category_id, category_name, icon |
| claims | `Claims` | ClaimID, ReportID, user_id, Message, ClaimStatus |

---

## 4. CRUD operations (requirement)

| Table | Create | Read | Update | Delete |
|-------|--------|------|--------|--------|
| `ItemReports` | `report-lost.php`, `report-found.php` → `report_action.php` | `index.php`, `browse.php`, `item-details.php`, `dashboard.php` | `edit-report.php` → `update_report.php`, `report_status.php` | `delete_report.php` (button on dashboard / details) |
| `Claims` | form on `item-details.php` → `claim_action.php` | `dashboard.php` | owner approves / rejects (`claim_action.php`) | claimant withdraws (`claim_action.php`) |
| `Student` | `login.php` → `regis_action.php`, `students.php` | `students.php` | `students.php?edit=ID` → `student_action.php` | `students.php` → `student_action.php` |
| `Category` | `categories.php` → `category_action.php` (admin) | dropdowns, cards, `categories.php` | `categories.php?edit=ID` (admin) | `categories.php` (admin; blocked while reports use it) |

---

## 5. Requirement checklist

| Requirement from the project statement | Where it is met |
|----------------------------------------|-----------------|
| Website with **at least 5 different webpages** | 11 pages: index, browse, item-details, report-lost, report-found, edit-report, login, dashboard, students, categories (PHP) + about (plain HTML) |
| **HTML5 and CSS** for structure and styling | semantic HTML5 in every page; `html/css/style.css` (responsive: 850 px and 600 px breakpoints) |
| **XAMPP** MySQL database + **PHP** server side | `sql/*.php` with `mysqli` prepared statements |
| **JavaScript** | `js/index.js` (instant search, filters, form validation, menu, confirm dialogs, image preview, counters, responsive tables) |
| MySQL database with **minimum 3 tables** | 4 tables (section 3) |
| **Each table at least 5 rows** | 6 / 6 / 8 / 5 rows (`sql/seed_data.php`, `sql/database.sql`) |
| **CRUD** operations | section 4 |
| Pages for inserting, updating, deleting and displaying data | report forms, edit page, delete buttons, browse/dashboard lists |
| **Code quality / documentation** | one config file, shared include files, a comment block at the top of every file, prepared statements, output escaping, CSRF tokens, password hashing |
| **User experience / responsive design** | mobile menu, stacked tables on phones, confirm dialogs, flash messages, live filters |

### Security measures
- Passwords stored with `password_hash` (bcrypt) and checked with `password_verify`.
- All SQL uses prepared statements (no SQL injection).
- All output is escaped with `htmlspecialchars` (no XSS).
- Every POST form carries a CSRF token; handlers reject GET requests.
- Users can only edit/delete their own reports; student and category management and the all-reports overview are for administrators only.
- Uploaded pictures are checked by real file type and limited to 1 MB (XAMPP's MySQL default packet size).

---

## 6. For the final submission (checklist)

- [ ] Word report: development process, challenges, **screenshots of the website** (upload to LMS)
- [ ] Compressed file (`.zip`) with the HTML/PHP files, CSS, JS and the MySQL database (`sql/database.sql`)
- [ ] Every team member can explain **every** part of the project (see the *Additional Notes* in the statement)

## 7. Team

| Name | Student ID |
|---|---|
| Faiz Alhaj | 202211121 |
| Khaled Wafa | 202410169 |
| Ibrahim Albaiz | 202121375 |
| Majed Alshuaibi | 202311375 |

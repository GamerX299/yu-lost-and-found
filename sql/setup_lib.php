<?php
/**
 * setup_lib.php - functions that build the database.
 *
 *   create_database()  CREATE DATABASE IF NOT EXISTS
 *   create_tables()    the 4 tables: Student, Category, ItemReports, Claims
 *   seed_database()    sample rows (at least 5 per table) - only into EMPTY tables
 *
 * They are called by db_creation.php, tableCreat.php, seed_data.php and by the
 * one-click sql/setup.php.  Every function returns an array of status messages.
 */
require_once __DIR__ . '/config.php';

function create_database(): array
{
    [, , , $name] = db_settings();
    $safe = str_replace('`', '``', $name);
    $conn = db_server();
    if (mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$safe` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        $msg = ["Database `$name` is ready."];
    } else {
        $msg = ['Error creating database: ' . mysqli_error($conn)];
    }
    mysqli_close($conn);
    return $msg;
}

function create_tables(): array
{
    $tables = [
        'Student' => "CREATE TABLE IF NOT EXISTS Student (
            user_id    INT PRIMARY KEY,
            user_name  VARCHAR(30)  NOT NULL UNIQUE,
            password   VARCHAR(255) NOT NULL,
            email      VARCHAR(100) NOT NULL UNIQUE,
            role       ENUM('student','admin') NOT NULL DEFAULT 'student',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'Category' => "CREATE TABLE IF NOT EXISTS Category (
            category_id   INT AUTO_INCREMENT PRIMARY KEY,
            category_name VARCHAR(50) NOT NULL UNIQUE,
            icon          VARCHAR(10) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'ItemReports' => "CREATE TABLE IF NOT EXISTS ItemReports (
            ReportID    INT AUTO_INCREMENT PRIMARY KEY,
            user_id     INT NOT NULL,
            category_id INT NOT NULL,
            Title       VARCHAR(100) NOT NULL,
            Location    VARCHAR(120) NOT NULL,
            ItemDate    DATE NOT NULL,
            Description TEXT NOT NULL,
            Contact     VARCHAR(120) DEFAULT NULL,
            Photo       LONGBLOB DEFAULT NULL,
            PhotoMime   VARCHAR(50) DEFAULT NULL,
            Status      ENUM('lost','found') NOT NULL,
            ReportState ENUM('open','returned') NOT NULL DEFAULT 'open',
            CreatedAt   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_reports_user     FOREIGN KEY (user_id)     REFERENCES Student(user_id)
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_reports_category FOREIGN KEY (category_id) REFERENCES Category(category_id)
                ON DELETE RESTRICT ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'Claims' => "CREATE TABLE IF NOT EXISTS Claims (
            ClaimID     INT AUTO_INCREMENT PRIMARY KEY,
            ReportID    INT NOT NULL,
            user_id     INT NOT NULL,
            Message     TEXT NOT NULL,
            ClaimStatus ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
            CreatedAt   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY one_claim_per_student (ReportID, user_id),
            CONSTRAINT fk_claims_report FOREIGN KEY (ReportID) REFERENCES ItemReports(ReportID)
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_claims_user   FOREIGN KEY (user_id)  REFERENCES Student(user_id)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    $messages = [];
    foreach ($tables as $name => $sql) {
        if (mysqli_query(db(), $sql)) {
            $messages[] = "Table $name is ready.";
        } else {
            $messages[] = "Error creating $name: " . mysqli_error(db());
        }
    }
    return $messages;
}

function seed_database(): array
{
    $messages = [];
    $count = fn(string $table) => (int) (db_select_one("SELECT COUNT(*) AS n FROM $table")['n'] ?? 0);

    // --- Student (6 rows) ------------------------------------------------
    if ($count('Student') === 0) {
        $students = [
            [1000,    'admin',     'admin@yu.edu', 'Admin@1234',  'admin'],
            [2210001, 'sara_ali',  'sara@yu.edu',  'Student@123', 'student'],
            [2210002, 'omar_k',    'omar@yu.edu',  'Student@123', 'student'],
            [2210003, 'laila_h',   'laila@yu.edu', 'Student@123', 'student'],
            [2210004, 'yousef_m',  'yousef@yu.edu','Student@123', 'student'],
            [2210005, 'noor_s',    'noor@yu.edu',  'Student@123', 'student'],
        ];
        foreach ($students as [$id, $name, $email, $pass, $role]) {
            db_exec('INSERT INTO Student (user_id, user_name, email, password, role) VALUES (?,?,?,?,?)',
                'issss', [$id, $name, $email, password_hash($pass, PASSWORD_DEFAULT), $role]);
        }
        $messages[] = 'Inserted 6 students.';
    }

    // --- Category (6 rows) -----------------------------------------------
    if ($count('Category') === 0) {
        $categories = [
            ['Electronics', '🎧'], ['Books & Notes', '📘'], ['Accessories', '🔑'],
            ['Clothing', '🧥'], ['Cards & Documents', '💳'], ['Other', '📦'],
        ];
        foreach ($categories as [$name, $icon]) {
            db_exec('INSERT INTO Category (category_name, icon) VALUES (?, ?)', 'ss', [$name, $icon]);
        }
        $messages[] = 'Inserted 6 categories.';
    }

    // --- ItemReports (8 rows) --------------------------------------------
    if ($count('ItemReports') === 0) {
        // user, category, title, location, date, description, status, state
        $reports = [
            [2210001, 1, 'Wireless headphones', 'Main Library',        '2026-11-18', 'Black wireless headphones, possibly left on a study desk in the library.', 'lost',  'open'],
            [2210002, 3, 'Set of keys',         'Student Center',      '2026-11-17', 'A set of keys with a red tag, found near the student center entrance.',      'found', 'open'],
            [2210003, 2, 'Blue notebook',       'Engineering Building','2026-11-16', 'Medium-sized blue notebook with lecture notes inside.',                       'lost',  'returned'],
            [2210004, 5, 'Student ID card',     'Lecture Hall B',      '2026-11-15', 'Student card found on the floor outside lecture hall B.',                    'found', 'open'],
            [2210005, 4, 'Grey jacket',         'Cafeteria',           '2026-11-14', 'Light grey zip jacket left on a chair in the cafeteria.',                    'lost',  'open'],
            [2210002, 6, 'Black umbrella',      'North Gate',          '2026-11-13', 'Black folding umbrella found near the north parking entrance.',              'found', 'returned'],
            [2210001, 6, 'Water bottle',        'Gym entrance',        '2026-11-12', 'Steel water bottle with a green cap found by the gym entrance.',             'found', 'open'],
            [2210003, 1, 'Scientific calculator','Room B204',          '2026-11-10', 'Casio fx-991 calculator with a name sticker on the back, lost after an exam.','lost', 'open'],
        ];
        foreach ($reports as [$uid, $cat, $title, $loc, $date, $desc, $status, $state]) {
            // CreatedAt = the item date, so the sample data sorts like real reports
            db_exec('INSERT INTO ItemReports (user_id, category_id, Title, Location, ItemDate, Description, Contact, Status, ReportState, CreatedAt)
                     VALUES (?,?,?,?,?,?,(SELECT email FROM Student WHERE user_id = ?),?,?,?)',
                'iissssisss', [$uid, $cat, $title, $loc, $date, $desc, $uid, $status, $state, $date . ' 12:00:00']);
        }
        $messages[] = 'Inserted 8 item reports.';
    }

    // --- Claims (5 rows) -------------------------------------------------
    if ($count('Claims') === 0 && $count('ItemReports') >= 6) {
        // report, claimant, message, status
        $claims = [
            [2, 2210001, 'These look like my keys - the red tag has my initials.',          'pending'],
            [4, 2210005, 'This is my student card, I lost it on Sunday.',                   'pending'],
            [6, 2210004, 'The umbrella is mine, I left it at the north gate after class.',  'approved'],
            [1, 2210002, 'I think I saw headphones like these in the library on Tuesday.',  'pending'],
            [5, 2210003, 'I believe I saw this jacket in the cafeteria lost-property box.', 'rejected'],
        ];
        foreach ($claims as [$report, $uid, $msg, $status]) {
            db_exec('INSERT INTO Claims (ReportID, user_id, Message, ClaimStatus) VALUES (?,?,?,?)',
                'iiss', [$report, $uid, $msg, $status]);
        }
        $messages[] = 'Inserted 5 claims.';
    }

    return $messages ?: ['Sample data already present - nothing inserted.'];
}

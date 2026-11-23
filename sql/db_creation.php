<?php
/**
 * db_creation.php - step 1 of the manual setup: creates the database.
 * (sql/setup.php does all three steps at once.)
 */
require_once __DIR__ . '/setup_lib.php';

foreach (create_database() as $message) {
    echo e($message) . '<br>';
}

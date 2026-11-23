<?php
/**
 * tableCreat.php - step 2 of the manual setup: creates the 4 tables
 * Student, Category, ItemReports and Claims.
 */
require_once __DIR__ . '/setup_lib.php';

foreach (create_tables() as $message) {
    echo e($message) . '<br>';
}

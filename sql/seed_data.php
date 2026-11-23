<?php
/**
 * seed_data.php - step 3 of the manual setup: inserts the sample rows
 * (6 students, 6 categories, 8 reports, 5 claims). Safe to run twice:
 * a table that already has rows is left alone.
 */
require_once __DIR__ . '/setup_lib.php';

foreach (seed_database() as $message) {
    echo e($message) . '<br>';
}

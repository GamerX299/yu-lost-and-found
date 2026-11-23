<?php
/**
 * category_action.php - CREATE / UPDATE / DELETE rows of the Category table.
 * Used only by categories.php, so every action requires the administrator role.
 * A category that is still used by reports cannot be deleted (foreign key RESTRICT).
 */
require_once __DIR__ . '/config.php';
require_admin('../html/dashboard.php');
require_post('../html/categories.php');

$action = post('action');
$id = (int) post('category_id');
$name = post('category_name');
$icon = post('icon');
$back = '../html/categories.php';

if ($action === 'delete') {
    $used = (int) db_select_one('SELECT COUNT(*) AS n FROM ItemReports WHERE category_id = ?', 'i', [$id])['n'];
    if ($used > 0) {
        flash('error', "This category is used by $used report(s). Move or delete those reports first.");
    } elseif (db_exec('DELETE FROM Category WHERE category_id = ?', 'i', [$id]) === false) {
        flash('error', 'Could not delete the category.');
    } else {
        flash('success', 'Category deleted.');
    }
    redirect($back);
}

if ($action !== 'create' && $action !== 'update') {
    flash('error', 'Unknown action.');
    redirect($back);
}

if (mb_strlen($name) < 2 || mb_strlen($name) > 50) {
    flash('error', 'Category name must be 2-50 characters.');
    redirect($action === 'update' ? $back . '?edit=' . $id : $back);
}
if ($icon === '' || mb_strlen($icon) > 4) {
    $icon = '📦';                                   // default picture for the cards
}

if ($action === 'create') {
    $ok = db_exec('INSERT INTO Category (category_name, icon) VALUES (?, ?)', 'ss', [$name, $icon]);
    flash($ok === false ? 'error' : 'success',
        $ok === false ? (db_errno() === 1062 ? 'That category already exists.' : 'Could not add the category.') : 'Category added.');
} else {
    $ok = db_exec('UPDATE Category SET category_name = ?, icon = ? WHERE category_id = ?', 'ssi', [$name, $icon, $id]);
    flash($ok === false ? 'error' : 'success',
        $ok === false ? (db_errno() === 1062 ? 'That category name is already used.' : 'Could not update the category.') : 'Category updated.');
}
redirect($back);

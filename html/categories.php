<?php
/**
 * categories.php - administrator page for the Category table (full CRUD).
 *   READ   : table of categories with the number of reports in each
 *   CREATE : "Add category" form
 *   UPDATE : "Edit" fills the same form (?edit=ID)
 *   DELETE : "Delete" button (blocked while reports still use the category)
 * Handler: sql/category_action.php. Only users with role = admin may open it.
 */
require_once __DIR__ . '/../sql/config.php';
require_admin();

$edit_id = (int) ($_GET['edit'] ?? 0);
$edit = $edit_id > 0 ? db_select_one('SELECT category_id, category_name, icon FROM Category WHERE category_id = ?', 'i', [$edit_id]) : null;

$categories = db_select(
    'SELECT c.category_id, c.category_name, c.icon,
            (SELECT COUNT(*) FROM ItemReports r WHERE r.category_id = c.category_id) AS reports
       FROM Category c ORDER BY c.category_name');

$page_title = 'Manage Categories';
$active = 'categories';
include __DIR__ . '/inc/header.php';
?>
<main>
    <section class="page-hero">
        <div class="container"><h1>Category management</h1><p>Administrator tools: add, rename and remove item categories.</p></div>
    </section>
    <section class="container content">
        <div class="form-layout">
            <div class="card table-wrap">
                <table>
                    <thead><tr><th>Icon</th><th>Category</th><th>Reports</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><?= e($c['icon']) ?></td>
                            <td class="table-title"><?= e($c['category_name']) ?></td>
                            <td><?= (int) $c['reports'] ?></td>
                            <td>
                                <div class="actions">
                                    <a class="btn-mini" href="categories.php?edit=<?= (int) $c['category_id'] ?>">Edit</a>
                                    <form method="post" action="../sql/category_action.php" class="inline-form"
                                          data-confirm="Delete the category &quot;<?= e($c['category_name']) ?>&quot;?">
                                        <?= csrf_field() ?><input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?= (int) $c['category_id'] ?>">
                                        <button class="btn-mini danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <form class="card form-card" method="post" action="../sql/category_action.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
                <?php if ($edit): ?><input type="hidden" name="category_id" value="<?= (int) $edit['category_id'] ?>"><?php endif; ?>
                <h2><?= $edit ? 'Edit category' : 'Add category' ?></h2>
                <p class="form-intro">The icon is shown on item cards without a photo.</p>
                <div class="auth-form">
                    <div class="form-group">
                        <label for="c-name">Category name *</label>
                        <input class="field" id="c-name" name="category_name" required minlength="2" maxlength="50" value="<?= e($edit['category_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="c-icon">Icon (an emoji)</label>
                        <input class="field" id="c-icon" name="icon" maxlength="4" placeholder="📦" value="<?= e($edit['icon'] ?? '') ?>">
                    </div>
                    <div class="form-actions">
                        <button class="btn" type="submit"><?= $edit ? 'Save changes' : 'Add category' ?></button>
                        <?php if ($edit): ?><a class="btn btn-outline" href="categories.php">Cancel</a><?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </section>
</main>
<?php include __DIR__ . '/inc/footer.php'; ?>

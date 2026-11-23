<?php
/**
 * students.php - administrator page for the Student table (full CRUD).
 *   READ   : table of all students
 *   CREATE : "Add student" form
 *   UPDATE : "Edit" fills the same form (?edit=ID)
 *   DELETE : "Delete" button
 * Handler: sql/student_action.php. Only users with role = admin may open it.
 */
require_once __DIR__ . '/../sql/config.php';
require_admin();

$edit_id = (int) ($_GET['edit'] ?? 0);
$edit = $edit_id > 0 ? db_select_one('SELECT user_id, user_name, email, role FROM Student WHERE user_id = ?', 'i', [$edit_id]) : null;

$students = db_select(
    'SELECT s.user_id, s.user_name, s.email, s.role, s.created_at,
            (SELECT COUNT(*) FROM ItemReports r WHERE r.user_id = s.user_id) AS reports
       FROM Student s ORDER BY s.role, s.user_id');

$page_title = 'Manage Students';
$active = 'students';
include __DIR__ . '/inc/header.php';
?>
<main>
    <section class="page-hero">
        <div class="container"><h1>Student management</h1><p>Administrator tools: add, edit and remove student accounts.</p></div>
    </section>
    <section class="container content">
        <div class="form-layout">
            <div class="card table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Reports</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td><?= (int) $s['user_id'] ?></td>
                            <td class="table-title"><?= e($s['user_name']) ?></td>
                            <td><?= e($s['email']) ?></td>
                            <td><span class="tag <?= $s['role'] === 'admin' ? 'tag-returned' : 'tag-found' ?>"><?= e(ucfirst($s['role'])) ?></span></td>
                            <td><?= (int) $s['reports'] ?></td>
                            <td>
                                <div class="actions">
                                    <a class="btn-mini" href="students.php?edit=<?= (int) $s['user_id'] ?>">Edit</a>
                                    <?php if ((int) $s['user_id'] !== current_user_id()): ?>
                                    <form method="post" action="../sql/student_action.php" class="inline-form"
                                          data-confirm="Delete <?= e($s['user_name']) ?> and all of their reports?">
                                        <?= csrf_field() ?><input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="u_id" value="<?= (int) $s['user_id'] ?>">
                                        <button class="btn-mini danger" type="submit">Delete</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <form class="card form-card" method="post" action="../sql/student_action.php" data-validate="student">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
                <h2><?= $edit ? 'Edit student' : 'Add student' ?></h2>
                <p class="form-intro"><?= $edit ? 'Leave the password empty to keep the current one.' : 'Create a new account.' ?></p>
                <div class="auth-form">
                    <div class="form-group">
                        <label for="s-id">Student ID *</label>
                        <input class="field" type="number" id="s-id" name="u_id" min="1" required value="<?= e($edit['user_id'] ?? '') ?>" <?= $edit ? 'readonly' : '' ?>>
                    </div>
                    <div class="form-group">
                        <label for="s-name">Username *</label>
                        <input class="field" id="s-name" name="u_name" required minlength="3" maxlength="30" pattern="[A-Za-z0-9_.]+" value="<?= e($edit['user_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="s-email">Email *</label>
                        <input class="field" type="email" id="s-email" name="u_email" required value="<?= e($edit['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="s-pass">Password <?= $edit ? '' : '*' ?></label>
                        <input class="field" type="password" id="s-pass" name="u_pass" minlength="8" <?= $edit ? '' : 'required' ?> autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label for="s-role">Role</label>
                        <select id="s-role" name="role">
                            <option value="student" <?= ($edit['role'] ?? '') === 'student' ? 'selected' : '' ?>>Student</option>
                            <option value="admin" <?= ($edit['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button class="btn" type="submit"><?= $edit ? 'Save changes' : 'Add student' ?></button>
                        <?php if ($edit): ?><a class="btn btn-outline" href="students.php">Cancel</a><?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </section>
</main>
<?php include __DIR__ . '/inc/footer.php'; ?>

<?php
/**
 * dashboard.php - the logged-in student's control panel.
 *   READ   : own reports, claims received on own reports, own claims, statistics
 *   UPDATE : edit a report, mark returned / re-open, approve or reject a claim
 *   DELETE : delete a report, withdraw a claim
 * All buttons post to handlers in sql/ (report_status, delete_report, claim_action).
 * Administrators also get a site-wide overview and a table of ALL reports.
 */
require_once __DIR__ . '/../sql/config.php';
require_login();

$uid = current_user_id();

$stats = db_select_one(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(Status = 'lost'), 0)  AS lost,
            COALESCE(SUM(Status = 'found'), 0) AS found,
            COALESCE(SUM(ReportState = 'returned'), 0) AS returned
       FROM ItemReports WHERE user_id = ?", 'i', [$uid]);

$my_reports = db_select(
    'SELECT r.ReportID, r.Title, r.Status, r.ReportState, r.ItemDate, c.category_name
       FROM ItemReports r JOIN Category c ON c.category_id = r.category_id
      WHERE r.user_id = ? ORDER BY r.CreatedAt DESC, r.ReportID DESC', 'i', [$uid]);

$received = db_select(
    'SELECT cl.ClaimID, cl.Message, cl.ClaimStatus, cl.CreatedAt, r.ReportID, r.Title, s.user_name
       FROM Claims cl
       JOIN ItemReports r ON r.ReportID = cl.ReportID
       JOIN Student s     ON s.user_id  = cl.user_id
      WHERE r.user_id = ? ORDER BY cl.CreatedAt DESC, cl.ClaimID DESC', 'i', [$uid]);

$my_claims = db_select(
    'SELECT cl.ClaimID, cl.Message, cl.ClaimStatus, r.ReportID, r.Title
       FROM Claims cl JOIN ItemReports r ON r.ReportID = cl.ReportID
      WHERE cl.user_id = ? ORDER BY cl.CreatedAt DESC, cl.ClaimID DESC', 'i', [$uid]);

// ---- administrator overview (site-wide) ----------------------------------
$site = $all_reports = [];
if (is_admin()) {
    $site = db_select_one(
        "SELECT (SELECT COUNT(*) FROM Student) AS students,
                (SELECT COUNT(*) FROM ItemReports) AS reports,
                (SELECT COUNT(*) FROM ItemReports WHERE ReportState = 'open') AS open_reports,
                (SELECT COUNT(*) FROM Claims WHERE ClaimStatus = 'pending') AS pending_claims");
    $all_reports = db_select(
        'SELECT r.ReportID, r.Title, r.Status, r.ReportState, r.ItemDate, s.user_name, c.category_name
           FROM ItemReports r
           JOIN Student  s ON s.user_id = r.user_id
           JOIN Category c ON c.category_id = r.category_id
          ORDER BY r.CreatedAt DESC, r.ReportID DESC');
}

$page_title = 'Dashboard';
$active = 'dashboard';
include __DIR__ . '/inc/header.php';
?>
<main>
    <section class="page-hero">
        <div class="container"><h1>Student dashboard</h1><p>Manage your lost and found reports in one place.</p></div>
    </section>
    <section class="container content">
        <div class="dashboard-head">
            <div>
                <h1>Hello, <?= e($_SESSION['user_name']) ?> 👋</h1>
                <p class="muted tight">Here is an overview of your activity.</p>
            </div>
            <div class="actions wrap">
                <?php if (is_admin()): ?>
                    <a class="btn btn-outline" href="students.php">Manage students</a>
                    <a class="btn btn-outline" href="categories.php">Manage categories</a>
                <?php endif; ?>
                <a class="btn" href="report-lost.php">＋ New report</a>
            </div>
        </div>

        <?php if (is_admin()): ?>
        <h2 class="section-title">Administrator overview</h2>
        <div class="dash-stats">
            <div class="card dash-stat"><span>Students</span><strong><?= (int) $site['students'] ?></strong></div>
            <div class="card dash-stat"><span>All reports</span><strong><?= (int) $site['reports'] ?></strong></div>
            <div class="card dash-stat"><span>Open reports</span><strong><?= (int) $site['open_reports'] ?></strong></div>
            <div class="card dash-stat"><span>Pending claims</span><strong><?= (int) $site['pending_claims'] ?></strong></div>
        </div>
        <div class="card table-wrap">
            <table>
                <thead><tr><th>Item</th><th>Posted by</th><th>Type</th><th>Category</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($all_reports as $r): ?>
                    <tr>
                        <td class="table-title"><a class="text-link" href="item-details.php?id=<?= (int) $r['ReportID'] ?>"><?= e($r['Title']) ?></a></td>
                        <td><?= e($r['user_name']) ?></td>
                        <td><?= e(ucfirst($r['Status'])) ?></td>
                        <td><?= e($r['category_name']) ?></td>
                        <td><?= e(fmt_date($r['ItemDate'])) ?></td>
                        <td><span class="tag <?= $r['ReportState'] === 'returned' ? 'tag-returned' : 'tag-' . e($r['Status']) ?>"><?= $r['ReportState'] === 'returned' ? 'Returned' : 'Open' ?></span></td>
                        <td>
                            <div class="actions">
                                <a class="btn-mini" href="edit-report.php?id=<?= (int) $r['ReportID'] ?>">Edit</a>
                                <form method="post" action="../sql/report_status.php" class="inline-form">
                                    <?= csrf_field() ?><input type="hidden" name="report_id" value="<?= (int) $r['ReportID'] ?>">
                                    <input type="hidden" name="state" value="<?= $r['ReportState'] === 'open' ? 'returned' : 'open' ?>">
                                    <button class="btn-mini" type="submit"><?= $r['ReportState'] === 'open' ? 'Mark returned' : 'Re-open' ?></button>
                                </form>
                                <form method="post" action="../sql/delete_report.php" class="inline-form" data-confirm="Delete &quot;<?= e($r['Title']) ?>&quot; posted by <?= e($r['user_name']) ?>?">
                                    <?= csrf_field() ?><input type="hidden" name="report_id" value="<?= (int) $r['ReportID'] ?>">
                                    <button class="btn-mini danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <h2 class="section-title">My activity</h2>
        <?php endif; ?>

        <div class="dash-stats">
            <div class="card dash-stat"><span>Total reports</span><strong><?= (int) $stats['total'] ?></strong></div>
            <div class="card dash-stat"><span>Lost items</span><strong><?= (int) $stats['lost'] ?></strong></div>
            <div class="card dash-stat"><span>Found items</span><strong><?= (int) $stats['found'] ?></strong></div>
            <div class="card dash-stat"><span>Returned</span><strong><?= (int) $stats['returned'] ?></strong></div>
        </div>

        <h2 class="section-title">My reports</h2>
        <?php if ($my_reports): ?>
        <div class="card table-wrap">
            <table>
                <thead><tr><th>Item</th><th>Type</th><th>Category</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($my_reports as $r): ?>
                    <tr>
                        <td class="table-title"><?= e($r['Title']) ?></td>
                        <td><?= e(ucfirst($r['Status'])) ?></td>
                        <td><?= e($r['category_name']) ?></td>
                        <td><?= e(fmt_date($r['ItemDate'])) ?></td>
                        <td><span class="tag <?= $r['ReportState'] === 'returned' ? 'tag-returned' : 'tag-' . e($r['Status']) ?>"><?= $r['ReportState'] === 'returned' ? 'Returned' : 'Open' ?></span></td>
                        <td>
                            <div class="actions">
                                <a class="btn-mini" href="item-details.php?id=<?= (int) $r['ReportID'] ?>">View</a>
                                <a class="btn-mini" href="edit-report.php?id=<?= (int) $r['ReportID'] ?>">Edit</a>
                                <form method="post" action="../sql/report_status.php" class="inline-form">
                                    <?= csrf_field() ?><input type="hidden" name="report_id" value="<?= (int) $r['ReportID'] ?>">
                                    <input type="hidden" name="state" value="<?= $r['ReportState'] === 'open' ? 'returned' : 'open' ?>">
                                    <button class="btn-mini" type="submit"><?= $r['ReportState'] === 'open' ? 'Mark returned' : 'Re-open' ?></button>
                                </form>
                                <form method="post" action="../sql/delete_report.php" class="inline-form" data-confirm="Delete &quot;<?= e($r['Title']) ?>&quot; permanently?">
                                    <?= csrf_field() ?><input type="hidden" name="report_id" value="<?= (int) $r['ReportID'] ?>">
                                    <button class="btn-mini danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="card empty-state"><div class="big">📝</div><p>You have not reported anything yet.</p>
                <a class="btn" href="report-lost.php">Report a lost item</a></div>
        <?php endif; ?>

        <h2 class="section-title">Claims on my items</h2>
        <?php if ($received): ?>
        <div class="card table-wrap">
            <table>
                <thead><tr><th>Item</th><th>From</th><th>Message</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($received as $c): ?>
                    <tr>
                        <td class="table-title"><a class="text-link" href="item-details.php?id=<?= (int) $c['ReportID'] ?>"><?= e($c['Title']) ?></a></td>
                        <td><?= e($c['user_name']) ?></td>
                        <td class="cell-wrap"><?= e($c['Message']) ?></td>
                        <td><span class="tag tag-<?= e($c['ClaimStatus']) ?>"><?= e(ucfirst($c['ClaimStatus'])) ?></span></td>
                        <td>
                            <?php if ($c['ClaimStatus'] === 'pending'): ?>
                            <div class="actions">
                                <?php foreach (['approve' => 'Approve', 'reject' => 'Reject'] as $act => $label): ?>
                                    <form method="post" action="../sql/claim_action.php" class="inline-form">
                                        <?= csrf_field() ?><input type="hidden" name="action" value="<?= $act ?>">
                                        <input type="hidden" name="claim_id" value="<?= (int) $c['ClaimID'] ?>">
                                        <button class="btn-mini <?= $act === 'reject' ? 'danger' : '' ?>" type="submit"><?= $label ?></button>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?><span class="hint">Done</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="card empty-state compact"><p>No one has sent a claim on your items yet.</p></div>
        <?php endif; ?>

        <h2 class="section-title">My claims</h2>
        <?php if ($my_claims): ?>
        <div class="card table-wrap">
            <table>
                <thead><tr><th>Item</th><th>My message</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($my_claims as $c): ?>
                    <tr>
                        <td class="table-title"><a class="text-link" href="item-details.php?id=<?= (int) $c['ReportID'] ?>"><?= e($c['Title']) ?></a></td>
                        <td class="cell-wrap"><?= e($c['Message']) ?></td>
                        <td><span class="tag tag-<?= e($c['ClaimStatus']) ?>"><?= e(ucfirst($c['ClaimStatus'])) ?></span></td>
                        <td>
                            <form method="post" action="../sql/claim_action.php" class="inline-form" data-confirm="Withdraw this claim?">
                                <?= csrf_field() ?><input type="hidden" name="action" value="delete">
                                <input type="hidden" name="claim_id" value="<?= (int) $c['ClaimID'] ?>">
                                <button class="btn-mini danger" type="submit">Withdraw</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="card empty-state compact"><p>You have not claimed any item. <a class="text-link" href="browse.php">Browse items →</a></p></div>
        <?php endif; ?>
    </section>
</main>
<?php include __DIR__ . '/inc/footer.php'; ?>

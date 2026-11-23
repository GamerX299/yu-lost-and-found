<?php
/**
 * item-details.php?id=N - full details of one report (READ) and the place where
 * a logged-in student can send a claim (CREATE in the Claims table).
 */
require_once __DIR__ . '/../sql/config.php';

$id = (int) ($_GET['id'] ?? 0);
$r = db_select_one(
    'SELECT r.*, (r.Photo IS NOT NULL) AS has_photo, c.category_name, c.icon, s.user_name
       FROM ItemReports r
       JOIN Category c ON c.category_id = r.category_id
       JOIN Student  s ON s.user_id = r.user_id
      WHERE r.ReportID = ?', 'i', [$id]);

if (!$r) {
    flash('error', 'That report does not exist (it may have been deleted).');
    redirect('browse.php');
}

$is_owner = is_logged_in() && (int) $r['user_id'] === current_user_id();
$can_manage = $is_owner || is_admin();
$my_claim = is_logged_in()
    ? db_select_one('SELECT ClaimID, Message, ClaimStatus FROM Claims WHERE ReportID = ? AND user_id = ?', 'ii', [$id, current_user_id()])
    : null;

$page_title = $r['Title'];
$active = 'browse';
include __DIR__ . '/inc/header.php';
?>
<main>
    <section class="page-hero">
        <div class="container"><h1>Item details</h1><p>Review the report information and contact the person who posted it.</p></div>
    </section>
    <section class="container content">
        <div class="detail-layout">
            <div class="detail-image">
                <?php if ($r['has_photo']): ?>
                    <img src="../sql/image.php?id=<?= (int) $r['ReportID'] ?>" alt="Photo of <?= e($r['Title']) ?>">
                <?php else: ?>
                    <?= e($r['icon']) ?>
                <?php endif; ?>
            </div>
            <article class="card detail-panel">
                <div class="item-meta">
                    <span>
                        <span class="tag tag-<?= e($r['Status']) ?>"><?= strtoupper(e($r['Status'])) ?> ITEM</span>
                        <?php if ($r['ReportState'] === 'returned'): ?><span class="tag tag-returned">RETURNED</span><?php endif; ?>
                    </span>
                    <span class="hint">Reported <?= e(fmt_date($r['CreatedAt'])) ?></span>
                </div>
                <h1><?= e($r['Title']) ?></h1>
                <p class="pre-line detail-text"><?= e($r['Description']) ?></p>
                <div class="detail-list">
                    <div><small>Category</small><strong><?= e($r['icon'] . ' ' . $r['category_name']) ?></strong></div>
                    <div><small><?= $r['Status'] === 'lost' ? 'Date lost' : 'Date found' ?></small><strong><?= e(fmt_date($r['ItemDate'])) ?></strong></div>
                    <div><small>Location</small><strong><?= e($r['Location']) ?></strong></div>
                    <div><small>Report status</small><strong><?= $r['ReportState'] === 'returned' ? 'Returned' : 'Open' ?></strong></div>
                    <div><small>Posted by</small><strong><?= e($r['user_name']) ?></strong></div>
                    <div><small>Contact</small><strong><?= is_logged_in() ? e($r['Contact'] ?: 'Not provided') : 'Log in to see' ?></strong></div>
                </div>
                <hr class="divider">

                <?php if ($can_manage): ?>
                    <h3 class="panel-title">Manage this report</h3>
                    <div class="actions wrap">
                        <a class="btn btn-sm" href="edit-report.php?id=<?= (int) $r['ReportID'] ?>">Edit</a>
                        <?php if ($r['ReportState'] === 'open'): ?>
                            <form method="post" action="../sql/report_status.php" class="inline-form">
                                <?= csrf_field() ?><input type="hidden" name="report_id" value="<?= (int) $r['ReportID'] ?>">
                                <input type="hidden" name="state" value="returned">
                                <button class="btn btn-outline btn-sm" type="submit">Mark as returned</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="../sql/report_status.php" class="inline-form">
                                <?= csrf_field() ?><input type="hidden" name="report_id" value="<?= (int) $r['ReportID'] ?>">
                                <input type="hidden" name="state" value="open">
                                <button class="btn btn-outline btn-sm" type="submit">Re-open</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="../sql/delete_report.php" class="inline-form" data-confirm="Delete this report permanently? This cannot be undone.">
                            <?= csrf_field() ?><input type="hidden" name="report_id" value="<?= (int) $r['ReportID'] ?>">
                            <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                        </form>
                    </div>
                <?php elseif (!is_logged_in()): ?>
                    <h3 class="panel-title">Do you recognize this item?</h3>
                    <p class="muted">Sign in to contact the student who submitted this report or submit a claim.</p>
                    <a class="btn" href="login.php">Log in to contact student →</a>
                <?php elseif ($my_claim): ?>
                    <h3 class="panel-title">Your claim</h3>
                    <p class="pre-line"><?= e($my_claim['Message']) ?></p>
                    <p><span class="tag tag-<?= e($my_claim['ClaimStatus']) ?>"><?= strtoupper(e($my_claim['ClaimStatus'])) ?></span></p>
                    <form method="post" action="../sql/claim_action.php" class="inline-form" data-confirm="Withdraw your claim?">
                        <?= csrf_field() ?><input type="hidden" name="action" value="delete">
                        <input type="hidden" name="claim_id" value="<?= (int) $my_claim['ClaimID'] ?>">
                        <button class="btn btn-outline btn-sm" type="submit">Withdraw claim</button>
                    </form>
                <?php elseif ($r['ReportState'] === 'returned'): ?>
                    <p class="muted">This item has already been returned to its owner.</p>
                <?php else: ?>
                    <h3 class="panel-title"><?= $r['Status'] === 'found' ? 'Is this yours?' : 'Have you seen this item?' ?></h3>
                    <p class="muted">Tell <?= e($r['user_name']) ?> how you can help. They will review your message.</p>
                    <form method="post" action="../sql/claim_action.php" class="auth-form">
                        <?= csrf_field() ?><input type="hidden" name="action" value="create">
                        <input type="hidden" name="report_id" value="<?= (int) $r['ReportID'] ?>">
                        <div class="form-group">
                            <label for="claim-message">Your message *</label>
                            <textarea id="claim-message" name="message" required minlength="10" maxlength="500" placeholder="Describe how you can prove it is yours, or where you saw it..."></textarea>
                        </div>
                        <button class="btn" type="submit"><?= $r['Status'] === 'found' ? 'Claim this item →' : 'Send message →' ?></button>
                    </form>
                <?php endif; ?>
            </article>
        </div>
    </section>
</main>
<?php include __DIR__ . '/inc/footer.php'; ?>

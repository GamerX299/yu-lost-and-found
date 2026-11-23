<?php
/**
 * index.php - home page. Statistics and the latest reports come from the database.
 */
require_once __DIR__ . '/../sql/config.php';

$total    = (int) db_select_one('SELECT COUNT(*) AS n FROM ItemReports')['n'];
$returned = (int) db_select_one("SELECT COUNT(*) AS n FROM ItemReports WHERE ReportState = 'returned'")['n'];
$students = (int) db_select_one('SELECT COUNT(*) AS n FROM Student')['n'];

$recent = db_select(
    'SELECT r.ReportID, r.Title, r.Location, r.ItemDate, r.Description, r.Status, r.ReportState,
            r.category_id, c.icon, (r.Photo IS NOT NULL) AS has_photo
       FROM ItemReports r JOIN Category c ON c.category_id = r.category_id
      ORDER BY r.CreatedAt DESC, r.ReportID DESC LIMIT 3');

$page_title = 'Home';
$active = 'home';
include __DIR__ . '/inc/header.php';
?>
<main>
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <span class="eyebrow">✦ A connected community</span>
                <h1>Lost something?<br>Let's bring it home.</h1>
                <p>A simple, trusted place for people to report lost belongings, share found items, and reconnect people with what matters.</p>
                <div class="hero-buttons">
                    <a class="btn btn-light" href="browse.php">Browse items&nbsp; →</a>
                    <a class="btn btn-outline" href="report-lost.php">＋ Report an item</a>
                </div>
            </div>
            <div class="hero-art">
                <div class="hero-icon">🎒</div>
                <div class="float-chip chip-one">🔎 Find your item</div>
                <div class="float-chip chip-two">🤝 Reunited with owners</div>
            </div>
        </div>
    </section>

    <section class="container">
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-icon">📦</div><div><strong><?= $total ?></strong><span>Items reported</span></div></div>
            <div class="stat-card"><div class="stat-icon">🤝</div><div><strong><?= $returned ?></strong><span>Items returned</span></div></div>
            <div class="stat-card"><div class="stat-icon">👥</div><div><strong><?= $students ?></strong><span>Community members</span></div></div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-head">
                <div><h2>Recently reported items</h2><p>Take a look at the latest lost and found reports.</p></div>
                <a class="text-link" href="browse.php">View all items →</a>
            </div>
            <?php if ($recent): ?>
                <div class="item-grid">
                    <?php foreach ($recent as $r) { include __DIR__ . '/inc/item_card.php'; } ?>
                </div>
            <?php else: ?>
                <div class="card empty-state"><div class="big">📭</div><p>No reports yet. Be the first to report an item.</p></div>
            <?php endif; ?>
        </div>
    </section>

    <section class="container section section-flush">
        <div class="cta-band">
            <div><h2>Found something?</h2><p>Your quick report could make someone's day.</p></div>
            <a class="btn" href="report-found.php">Report a found item →</a>
        </div>
    </section>
</main>
<?php include __DIR__ . '/inc/footer.php'; ?>

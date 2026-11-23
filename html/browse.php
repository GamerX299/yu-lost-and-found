<?php
/**
 * browse.php - searchable list of every report (READ).
 * Filters come from the query string: ?q=keyword&type=lost|found&cat=ID
 */
require_once __DIR__ . '/../sql/config.php';

$q    = trim((string) ($_GET['q'] ?? ''));
$type = (string) ($_GET['type'] ?? '');
$cat  = (int) ($_GET['cat'] ?? 0);

$sql = 'SELECT r.ReportID, r.Title, r.Location, r.ItemDate, r.Description, r.Status, r.ReportState,
               r.category_id, c.icon, (r.Photo IS NOT NULL) AS has_photo
          FROM ItemReports r JOIN Category c ON c.category_id = r.category_id
         WHERE 1 = 1';
$types = '';
$params = [];
if ($type === 'lost' || $type === 'found') {
    $sql .= ' AND r.Status = ?';
    $types .= 's'; $params[] = $type;
}
if ($cat > 0) {
    $sql .= ' AND r.category_id = ?';
    $types .= 'i'; $params[] = $cat;
}
if ($q !== '') {
    $sql .= ' AND (r.Title LIKE ? OR r.Description LIKE ? OR r.Location LIKE ?)';
    $like = '%' . $q . '%';
    $types .= 'sss'; array_push($params, $like, $like, $like);
}
$sql .= ' ORDER BY r.CreatedAt DESC, r.ReportID DESC LIMIT 60';

$items = db_select($sql, $types, $params);
$categories = db_select('SELECT category_id, category_name FROM Category ORDER BY category_name');

$page_title = 'Browse Items';
$active = 'browse';
include __DIR__ . '/inc/header.php';
?>
<main>
    <section class="page-hero">
        <div class="container"><h1>Browse items</h1><p>Search reported belongings and see if something belongs to you.</p></div>
    </section>
    <section class="container content">
        <form class="toolbar" method="get" action="browse.php" id="filter-form">
            <div class="searchbox">
                <span class="search-symbol">⌕</span>
                <input class="field" type="search" name="q" value="<?= e($q) ?>" data-live-search="results" placeholder="Search by item name, place or keyword...">
            </div>
            <select name="type" aria-label="Filter by type" class="filter-select">
                <option value="">All reports</option>
                <option value="lost" <?= $type === 'lost' ? 'selected' : '' ?>>Lost items</option>
                <option value="found" <?= $type === 'found' ? 'selected' : '' ?>>Found items</option>
            </select>
            <select name="cat" aria-label="Filter by category" class="filter-select">
                <option value="0">All categories</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['category_id'] ?>" <?= $cat === (int) $c['category_id'] ? 'selected' : '' ?>><?= e($c['category_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn" type="submit">Search</button>
            <?php if ($q !== '' || $type !== '' || $cat > 0): ?>
                <a class="btn btn-outline" href="browse.php">Clear</a>
            <?php endif; ?>
        </form>

        <p class="result-count" id="result-count"><?= count($items) ?> item<?= count($items) === 1 ? '' : 's' ?> found</p>

        <?php if ($items): ?>
            <div class="item-grid" id="results">
                <?php foreach ($items as $r) { include __DIR__ . '/inc/item_card.php'; } ?>
            </div>
        <?php else: ?>
            <div class="card empty-state"><div class="big">🔍</div><p>No items match your search. Try different keywords or clear the filters.</p></div>
        <?php endif; ?>
    </section>
</main>
<?php include __DIR__ . '/inc/footer.php'; ?>

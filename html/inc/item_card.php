<?php
/**
 * inc/item_card.php - one report card (used on index.php and browse.php).
 * Expects $r = row with: ReportID, Title, Location, ItemDate, Description,
 *                        Status, ReportState, category_id, icon, has_photo
 */
$short = mb_strlen($r['Description']) > 90 ? mb_substr($r['Description'], 0, 90) . '…' : $r['Description'];
?>
<article class="card item-card">
    <div class="item-visual <?= e(visual_class((int) $r['category_id'])) ?>">
        <?php if (!empty($r['has_photo'])): ?>
            <img src="../sql/image.php?id=<?= (int) $r['ReportID'] ?>" alt="Photo of <?= e($r['Title']) ?>" loading="lazy">
        <?php else: ?>
            <?= e($r['icon']) ?>
        <?php endif; ?>
    </div>
    <div class="item-body">
        <div class="item-meta">
            <span>
                <span class="tag tag-<?= e($r['Status']) ?>"><?= strtoupper(e($r['Status'])) ?></span>
                <?php if ($r['ReportState'] === 'returned'): ?><span class="tag tag-returned">RETURNED</span><?php endif; ?>
            </span>
            <span class="hint"><?= e(fmt_date($r['ItemDate'])) ?></span>
        </div>
        <h3><?= e($r['Title']) ?></h3>
        <p><?= e($short) ?></p>
        <div class="item-foot">
            <span>📍 <?= e($r['Location']) ?></span>
            <a class="text-link" href="item-details.php?id=<?= (int) $r['ReportID'] ?>">View details →</a>
        </div>
    </div>
</article>

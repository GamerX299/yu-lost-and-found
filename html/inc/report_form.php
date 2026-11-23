<?php
/**
 * inc/report_form.php - the lost/found form, shared by three pages:
 *   report-lost.php, report-found.php  (create)  and  edit-report.php  (update)
 *
 * Expected variables:
 *   $mode    'lost' | 'found'
 *   $report  existing row when editing, otherwise null
 */
$editing = !empty($report);
$action = $editing ? '../sql/update_report.php' : '../sql/report_action.php';
$val = fn(string $k, string $default = '') => e($report[$k] ?? $default);
$categories = db_select('SELECT category_id, category_name FROM Category ORDER BY category_name');
$date_label = $mode === 'lost' ? 'Date lost' : 'Date found';
?>
<form class="card form-card" action="<?= e($action) ?>" method="post" enctype="multipart/form-data" data-validate="report">
    <?= csrf_field() ?>
    <input type="hidden" name="status" value="<?= e($mode) ?>">
    <?php if ($editing): ?>
        <input type="hidden" name="report_id" value="<?= (int) $report['ReportID'] ?>">
    <?php endif; ?>
    <h2><?= $editing ? 'Edit your report' : 'Item information' ?></h2>
    <p class="form-intro">Fields marked with * are required.</p>
    <div class="form-grid">
        <div class="form-group full">
            <label for="title">Item name *</label>
            <input class="field" id="title" name="title" required maxlength="100" value="<?= $val('Title') ?>" placeholder="e.g. Black wireless headphones">
        </div>
        <div class="form-group">
            <label for="category">Category *</label>
            <select id="category" name="category_id" required>
                <option value="">Choose a category</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['category_id'] ?>" <?= (int) ($report['category_id'] ?? 0) === (int) $c['category_id'] ? 'selected' : '' ?>>
                        <?= e($c['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="date"><?= e($date_label) ?> *</label>
            <input class="field" type="date" id="date" name="item_date" required max="<?= date('Y-m-d') ?>" value="<?= $val('ItemDate', date('Y-m-d')) ?>">
        </div>
        <div class="form-group full">
            <label for="location">Location *</label>
            <input class="field" id="location" name="location" required maxlength="120" value="<?= $val('Location') ?>" placeholder="Where was it last seen / found?">
        </div>
        <div class="form-group full">
            <label for="description">Description *</label>
            <textarea id="description" name="description" required maxlength="1000" data-counter="description-count" placeholder="Describe the item, including identifying details..."><?= $val('Description') ?></textarea>
            <span class="hint"><span id="description-count">0</span> / 1000 characters</span>
        </div>
        <div class="form-group full">
            <label for="image">Upload an image</label>
            <input class="field" id="image" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" data-preview="image-preview">
            <span class="hint">JPG, PNG, GIF or WebP. Large photos are shrunk automatically. A clear image helps identify the item.</span>
            <img id="image-preview" class="upload-preview" alt="Preview of the selected image" hidden>
            <?php if ($editing && !empty($report['has_photo'])): ?>
                <label class="check"><input type="checkbox" name="remove_photo" value="1"> Remove the current image</label>
            <?php endif; ?>
        </div>
        <div class="form-group full">
            <label for="contact">Contact information</label>
            <input class="field" id="contact" name="contact" maxlength="120" value="<?= $val('Contact', $_SESSION['user_email'] ?? '') ?>" placeholder="Email or preferred contact">
        </div>
        <div class="form-group full form-actions">
            <button class="btn" type="submit">
                <?= $editing ? 'Save changes →' : 'Submit ' . e($mode) . ' report →' ?>
            </button>
            <?php if ($editing): ?>
                <a class="btn btn-outline" href="item-details.php?id=<?= (int) $report['ReportID'] ?>">Cancel</a>
            <?php endif; ?>
        </div>
    </div>
</form>

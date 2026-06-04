<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_role('admin');
$pageTitle = 'Categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'add');
    $id = (int) ($_POST['category_id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $used = db()->prepare('SELECT COUNT(*) FROM services WHERE category_id = ?');
        $used->execute([$id]);
        if ((int) $used->fetchColumn() > 0) {
            flash_set('error', 'Category has services — cannot delete.');
        } else {
            db()->prepare('DELETE FROM service_categories WHERE id = ?')->execute([$id]);
            flash_set('success', 'Category deleted.');
        }
    } elseif ($action === 'edit' && $id > 0) {
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name !== '') {
            db()->prepare('UPDATE service_categories SET name = ?, slug = ? WHERE id = ?')
                ->execute([$name, slugify($name), $id]);
            flash_set('success', 'Category updated.');
        }
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name !== '') {
            db()->prepare('INSERT INTO service_categories (name, slug) VALUES (?, ?)')
                ->execute([$name, slugify($name)]);
            flash_set('success', 'Category added.');
        }
    }
    redirect('categories.php');
}

$categories = db()->query('SELECT * FROM service_categories ORDER BY sort_order, name')->fetchAll();

require dirname(__DIR__) . '/includes/admin_header.php';
?>

    <h1>Service categories</h1>
    <form class="form inline-form panel" method="post" style="max-width:420px;margin-bottom:1.5rem;">
        <input type="hidden" name="action" value="add" />
        <input type="text" name="name" placeholder="New category name" required />
        <button type="submit" class="btn btn--primary btn--sm">Add category</button>
    </form>

    <table class="data-table">
        <thead><tr><th>Name</th><th>Slug</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($categories as $c) : ?>
            <tr>
                <td>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="action" value="edit" />
                        <input type="hidden" name="category_id" value="<?= (int) $c['id'] ?>" />
                        <input type="text" name="name" value="<?= e($c['name']) ?>" required />
                        <button type="submit" class="btn btn--ghost btn--sm">Save</button>
                    </form>
                </td>
                <td><?= e($c['slug']) ?></td>
                <td>
                    <form method="post" class="inline-form" onsubmit="return confirm('Delete category?');">
                        <input type="hidden" name="action" value="delete" />
                        <input type="hidden" name="category_id" value="<?= (int) $c['id'] ?>" />
                        <button type="submit" class="btn btn--ghost btn--sm">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>

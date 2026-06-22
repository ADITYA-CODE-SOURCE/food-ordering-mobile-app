<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

require_post_request('foods.php');
require_csrf('foods.php');

$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    $food = fetch_one($pdo, 'SELECT id, image FROM foods WHERE id = ?', [$id]);
    if ($food) {
        $references = fetch_one(
            $pdo,
            'SELECT
                (SELECT COUNT(*) FROM order_items WHERE food_id = ?) AS order_items_count,
                (SELECT COUNT(*) FROM cart WHERE food_id = ?) AS cart_count,
                (SELECT COUNT(*) FROM favorites WHERE food_id = ?) AS favorites_count,
                (SELECT COUNT(*) FROM reviews WHERE food_id = ?) AS reviews_count,
                (SELECT COUNT(*) FROM recently_viewed WHERE food_id = ?) AS recently_viewed_count',
            [$id, $id, $id, $id, $id]
        );

        $hasReferences = array_sum(array_map('intval', $references ?: [])) > 0;

        $pdo->beginTransaction();
        try {
            if ($hasReferences) {
                $pdo->prepare('UPDATE foods SET availability_status = "sold_out", is_featured = 0, is_recommended = 0, is_popular = 0, is_trending = 0, is_new_arrival = 0 WHERE id = ?')->execute([$id]);
                $pdo->commit();
                set_flash('success', 'Food archived because it has existing customer or order history.');
            } else {
                $pdo->prepare('DELETE FROM foods WHERE id = ?')->execute([$id]);
                $pdo->commit();
                delete_local_upload($food['image'] ?? null);
                set_flash('success', 'Food deleted successfully.');
            }
        } catch (Throwable $exception) {
            $pdo->rollBack();
            set_flash('error', 'Unable to delete the selected food right now.');
        }
    }
}
redirect('foods.php');

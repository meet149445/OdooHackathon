<?php
require_once 'config.php';
requireLogin();

$input = getJsonBody();
$action = $_REQUEST['action'] ?? 'list';
$userId = currentUserId();

// Packing stored per-user and per-trip.
// Tables expected:
// - packing_items(id, user_id, trip_id, category, item_text, packed, created_at, updated_at)

switch ($action) {
    case 'list':
        $tripId = isset($_REQUEST['trip_id']) ? intval($_REQUEST['trip_id']) : intval($input['trip_id'] ?? 0);

        if ($tripId) {
            $stmt = $pdo->prepare('SELECT * FROM packing_items WHERE user_id = ? AND trip_id = ? ORDER BY category, id');
            $stmt->execute([$userId, $tripId]);
        } else {
            $stmt = $pdo->prepare('SELECT * FROM packing_items WHERE user_id = ? ORDER BY trip_id, category, id');
            $stmt->execute([$userId]);
        }

        $items = $stmt->fetchAll();

        $categories = [
            'clothing' => [],
            'documents' => [],
            'electronics' => [],
            'toiletries' => [],
            'other' => []
        ];

        foreach ($items as $it) {
            $cat = $it['category'];
            if (!isset($categories[$cat])) {
                $categories[$cat] = [];
            }
            $categories[$cat][] = $it;
        }

        jsonResponse(['success' => true, 'categories' => $categories]);
        break;

    case 'upsert':
        $tripId = intval($input['trip_id'] ?? 0);
        $category = trim($input['category'] ?? 'other');
        $itemText = trim($input['item'] ?? '');
        $packed = intval($input['packed'] ?? 0);

        if (!$tripId) {
            jsonResponse(['success' => false, 'message' => 'trip_id is required.'], 400);
        }
        if (!$itemText) {
            jsonResponse(['success' => false, 'message' => 'item is required.'], 400);
        }

        $stmt = $pdo->prepare('SELECT id FROM packing_items WHERE user_id = ? AND trip_id = ? AND category = ? AND item_text = ? LIMIT 1');
        $stmt->execute([$userId, $tripId, $category, $itemText]);
        $existing = $stmt->fetch();

        if ($existing) {
            $upd = $pdo->prepare('UPDATE packing_items SET packed = ?, updated_at = NOW() WHERE id = ?');
            $upd->execute([$packed, intval($existing['id'])]);
            jsonResponse(['success' => true, 'message' => 'Packing item updated.']);
        } else {
            $ins = $pdo->prepare('INSERT INTO packing_items (user_id, trip_id, category, item_text, packed, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
            $ins->execute([$userId, $tripId, $category, $itemText, $packed]);
            jsonResponse(['success' => true, 'message' => 'Packing item added.', 'item_id' => intval($pdo->lastInsertId())]);
        }
        break;

    case 'toggle':
        $tripId = intval($input['trip_id'] ?? 0);
        $itemId = intval($input['item_id'] ?? 0);

        if (!$tripId) {
            jsonResponse(['success' => false, 'message' => 'trip_id is required.'], 400);
        }
        if (!$itemId) {
            jsonResponse(['success' => false, 'message' => 'item_id is required.'], 400);
        }

        $stmt = $pdo->prepare('SELECT packed FROM packing_items WHERE id = ? AND user_id = ? AND trip_id = ?');
        $stmt->execute([$itemId, $userId, $tripId]);
        $row = $stmt->fetch();

        if (!$row) {
            jsonResponse(['success' => false, 'message' => 'Packing item not found.'], 404);
        }

        $newPacked = intval(!$row['packed']);

        $upd = $pdo->prepare('UPDATE packing_items SET packed = ?, updated_at = NOW() WHERE id = ? AND user_id = ? AND trip_id = ?');
        $upd->execute([$newPacked, $itemId, $userId, $tripId]);

        jsonResponse(['success' => true, 'packed' => $newPacked]);
        break;

    case 'delete':
        $tripId = intval($input['trip_id'] ?? 0);
        $itemId = intval($input['item_id'] ?? 0);

        if (!$tripId) {
            jsonResponse(['success' => false, 'message' => 'trip_id is required.'], 400);
        }
        if (!$itemId) {
            jsonResponse(['success' => false, 'message' => 'item_id is required.'], 400);
        }

        $del = $pdo->prepare('DELETE FROM packing_items WHERE id = ? AND user_id = ? AND trip_id = ?');
        $del->execute([$itemId, $userId, $tripId]);

        jsonResponse(['success' => true, 'message' => 'Packing item deleted.']);
        break;

    case 'reset':
        $tripId = intval($input['trip_id'] ?? 0);
        if (!$tripId) {
            jsonResponse(['success' => false, 'message' => 'trip_id is required.'], 400);
        }

        $del = $pdo->prepare('DELETE FROM packing_items WHERE user_id = ? AND trip_id = ?');
        $del->execute([$userId, $tripId]);

        jsonResponse(['success' => true, 'message' => 'Packing reset.']);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Unknown packing action.'], 400);
}






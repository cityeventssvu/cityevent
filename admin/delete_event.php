<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php'; 

$event_id = $_GET['id'] ?? null;

if (!$event_id || !is_numeric($event_id)) {
    header('Location: dashboard.php?msg=error');
    exit;
}
// try to delete event
try {
    $stmt_fetch = $pdo->prepare("SELECT image_path FROM events WHERE id = :id");
    $stmt_fetch->execute(['id' => $event_id]);
    $event = $stmt_fetch->fetch();
    
    $image_deleted = false;

    if ($event && $event['image_path']) {
        $file_path = __DIR__ . '/' . $event['image_path'];
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                $image_deleted = true;
            } else {
                error_log("Failed to delete image file: " . $file_path);
            }
        }
    }

    $stmt_delete = $pdo->prepare("DELETE FROM events WHERE id = :id");
    $stmt_delete->execute(['id' => $event_id]);
    // check if event was deleted
    if ($stmt_delete->rowCount() > 0) {
        header('Location: dashboard.php?msg=deleted');
        exit;
    } 
    // if event was not deleted, redirect to dashboard with error message
    header('Location: dashboard.php?msg=error');
    exit;
// catch database error
} catch (PDOException $e) {
    error_log('Database Delete Error: ' . $e->getMessage());
    header('Location: dashboard.php?msg=error');
    exit;
}
?>
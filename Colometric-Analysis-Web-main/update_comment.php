<?php
require_once 'DATABASE/function.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $comment = $_POST['comment'] ?? '';
    if ($id) {
        $db->update('history', ['comment' => $comment], ['id' => $id]);
        echo 'ok';
    } else {
        http_response_code(400);
        echo 'Missing ID';
    }
}
?>

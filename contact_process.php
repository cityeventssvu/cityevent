// Contact form processing
<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php?msg=error');
    exit;
}

// Helper and validate
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

$valid = true;

// Validate name
if ($name === '' || mb_strlen($name) < 2) {
    $valid = false;
}

// Validate email
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $valid = false;
}

// Validate message
if ($message === '' || mb_strlen($message) < 5) {
    $valid = false;
}

// If valid, process the message
if ($valid) {
    
    header('Location: contact.php?msg=sent');
    exit;
}

header('Location: contact.php?msg=error');
exit;

<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php'; 

$upload_dir = __DIR__ . '/../uploads/';
$allowed_categories = ['Culture', 'Sports', 'Music', 'Family'];

$errors = [];
$form_data = [];
$success_message = '';

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // get form data
    $form_data['title'] = trim($_POST['title'] ?? '');
    $form_data['description'] = trim($_POST['description'] ?? '');
    $form_data['category'] = trim($_POST['category'] ?? '');
    $form_data['location'] = trim($_POST['location'] ?? '');
    $form_data['event_date'] = trim($_POST['date'] ?? '');
    
    $image_path = null;

    // validate form data
    if (empty($form_data['title'])) { $errors['title'] = 'Title is required.'; }
    if (empty($form_data['description'])) { $errors['description'] = 'Description is required.'; }
    if (empty($form_data['location'])) { $errors['location'] = 'Location is required.'; }
    if (empty($form_data['event_date'])) { $errors['date'] = 'Date is required.'; }
    if (!in_array($form_data['category'], $allowed_categories)) { 
        $errors['category'] = 'Invalid category selected.'; 
    }

    // check if image is uploaded
    if (empty($errors) && isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // get file name
        $file_name = $_FILES['image']['name'];
        // get file temporary name
        $file_tmp = $_FILES['image']['tmp_name'];
        // get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        // generate unique name
        $unique_name = time() . '-' . uniqid() . '.' . $file_ext;
        // generate destination
        $destination = $upload_dir . $unique_name;
        // generate image path
        $image_path = 'uploads/' . $unique_name;

        // allowed extensions
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        // file extension check
        if (!in_array($file_ext, $allowed_exts)) {
            $errors['image'] = 'Only JPG, JPEG, PNG, and GIF files are allowed.';
        } elseif ($_FILES['image']['size'] > 5000000) {
            $errors['image'] = 'File size must be less than 5MB.'; 
        } elseif (!move_uploaded_file($file_tmp, $destination)) {
            $errors['image'] = 'Failed to upload image.';
        }
        // image upload failed with error code
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
        $errors['image'] = 'Image upload failed with error code: ' . $_FILES['image']['error'];
    }
    
    if (empty($errors)) {
        try {
            // insert event
            $sql = "INSERT INTO events (title, description, category, location, event_date, image_path) 
                    VALUES (:title, :description, :category, :location, :event_date, :image_path)";
            
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'title' => $form_data['title'],
                'description' => $form_data['description'],
                'category' => $form_data['category'],
                'location' => $form_data['location'],
                'event_date' => $form_data['event_date'],
                'image_path' => $image_path,
            ]);

            header('Location: dashboard.php?msg=added');
            exit;

        } catch (PDOException $e) {
            $errors['db'] = 'Database error: Could not add event';
        }
    }
}

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3">Add New Event</h2>
    <a href="dashboard.php" class="btn btn-outline-secondary">
        &larr; Back to dashboard
    </a>
</div>

<?php if (isset($errors['db'])): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($errors['db']) ?>
    </div>
<?php endif; ?>

<div class="card p-4 shadow-sm">
    <form method="POST" enctype="multipart/form-data">
        
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" id="title" name="title" required
                   value="<?= htmlspecialchars($form_data['title'] ?? '') ?>">
            <?php if (isset($errors['title'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['title']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" id="description" name="description" rows="4" required><?= htmlspecialchars($form_data['description'] ?? '') ?></textarea>
            <?php if (isset($errors['description'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['description']) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>" id="category" name="category" required>
                    <option value="">Choose...</option>
                    <?php foreach ($allowed_categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" 
                            <?= (isset($form_data['category']) && $form_data['category'] === $cat) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['category']) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-4 mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control <?= isset($errors['location']) ? 'is-invalid' : '' ?>" id="location" name="location" required
                       value="<?= htmlspecialchars($form_data['location'] ?? '') ?>">
                <?php if (isset($errors['location'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['location']) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-4 mb-3">
                <label for="date" class="form-label">Date and Time</label>
                <input type="datetime-local" class="form-control <?= isset($errors['date']) ? 'is-invalid' : '' ?>" id="date" name="date" required
                       value="<?= htmlspecialchars($form_data['event_date'] ?? '') ?>">
                <?php if (isset($errors['date'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['date']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-4">
            <label for="image" class="form-label">Event Image</label>
            <input type="file" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" id="image" name="image" accept="image/*">
            <div class="form-text">Max 5MB. Leave blank to skip image upload.</div>
            <?php if (isset($errors['image'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['image']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">
            Add Event
        </button>
    </form>
</div>

<?php
// get add event content
$add_event_content = ob_get_clean();
?>

<!-- add event form using bootstrap -->
<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add New Event</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="icon" href="../assets/img/fav.png" type="image/png">

    <style>
        body {
            transition: background-color 0.3s, color 0.3s;
        }
        .navbar {
            border-bottom: 1px solid var(--bs-border-color-translucent);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-body">

<nav class="navbar navbar-expand-lg bg-body-tertiary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">City Events Dashboard</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="add_event.php">Add Event</a>
                </li>
            </ul>

            <div class="d-flex align-items-center">
                <button id="theme-toggle" class="btn btn-sm btn-outline-primary me-3" title="Toggle theme">
                    <svg class="bi" width="16" height="16" fill="currentColor">
                        <use href="#sun-fill"></use>
                    </svg>
                </button>
                <a class="btn btn-sm btn-danger" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>
<main class="container py-4">
    <?= $add_event_content ?>
</main>
<footer class="footer mt-auto py-3 bg-body">
    <div class="container text-center">
        <span class="text-muted">&copy; 2025 City Events Dashboard</span>
    </div>
</footer>
<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
    <symbol id="sun-fill" viewBox="0 0 16 16">
        <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
    </symbol>
    <symbol id="moon-stars-fill" viewBox="0 0 16 16">
        <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277A.768.768 0 0 1 15.858 13a7.208 7.208 0 0 1-7.962-7.962 7.208 7.208 0 0 0 4.218-3.535.79.79 0 0 1 .858-.08zm.287 4.197a.768.768 0 0 0-.256-.051A6.993 6.993 0 0 1 5.166 8.3c0 1.05.158 2.07.458 3.018A.768.768 0 0 0 6 12.046a.795.795 0 0 1-.758 1.157 8.163 8.163 0 0 0 4.153-2.022.774.774 0 0 1 .288-.231 7.272 7.272 0 0 0 2.217-5.06A.768.768 0 0 0 12.046 6.002a.795.795 0 0 1-1.157-.758A8.163 8.163 0 0 0 6.73 3.824a.774.774 0 0 1-.23.288z"/>
    </symbol>
</svg>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="theme_toggle.js"></script>

</body>
</html>
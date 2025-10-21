<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php'; 

$alert = '';
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
    $messages = [
        'added'   => ['success', 'Event successfully added'],
        'updated' => ['success', 'Event successfully updated'],
        'deleted' => ['warning', 'Event deleted'],
        'error'   => ['danger',  'An unexpected error occurred'],
    ];

    if (isset($messages[$msg])) {
        list($type, $text) = $messages[$msg];
        // add alert to $alert variable
        $alert = '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                      ' . $text . '
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
    }
}

// try to get events
try {
    $stmt = $pdo->query("SELECT id, title, event_date, location, category FROM events ORDER BY event_date DESC");
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    // if database error, set events to empty array and add alert
    $events = [];
    // add alert to $alert variable
    $alert .= '<div class="alert alert-danger mt-3" role="alert">Could not load events: Database error.</div>';
}

// start output buffering
ob_start();
?>

<!-- show alert -->
<?= $alert ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0">Event Management Dashboard</h2>
    <div class="d-flex gap-2">
        
        <a href="add_event.php" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg me-1" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
            </svg>
            Add event
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Title</th>
                <th scope="col">Date</th>
                <th scope="col">Location</th>
                <th scope="col" class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- show events -->
            <?php if (count($events) > 0): ?>
                <?php foreach ($events as $event): ?>
                <tr>
                    <th scope="row"><?= htmlspecialchars($event['id']) ?></th>
                    <td><?= htmlspecialchars($event['title']) ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($event['event_date'])) ?></td>
                    <td><?= htmlspecialchars($event['location']) ?></td>
                    <td class="text-center">
                        <div class="d-flex gap-2">
                            <a href="edit_event.php?id=<?= htmlspecialchars($event['id']) ?>" class="btn btn-sm btn-outline-secondary me-2">Edit</a>
                            
                            <button type="button" class="btn btn-sm btn-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteModal"
                                    data-event-id="<?= htmlspecialchars($event['id']) ?>"
                                    data-event-title="<?= htmlspecialchars($event['title']) ?>">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="alert alert-info mb-0" role="alert">No events found. Add one now!</div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the event: <strong id="modal-event-title"></strong> (ID: <span id="modal-event-id"></span>)? This action cannot be undone 
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="modal-delete-link" href="#" class="btn btn-danger">Delete Event</a>
            </div>
        </div>
    </div>
</div>

<!-- delete event modal -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const eventId = button.getAttribute('data-event-id');
                const eventTitle = button.getAttribute('data-event-title');
                const modalEventTitle = deleteModal.querySelector('#modal-event-title');
                const modalEventId = deleteModal.querySelector('#modal-event-id');
                const modalDeleteLink = deleteModal.querySelector('#modal-delete-link');
                modalEventTitle.textContent = eventTitle;
                modalEventId.textContent = eventId;
                modalDeleteLink.href = 'delete_event.php?id=' + eventId;
            });
        }
    });
</script>

<?php
$dashboard_content = ob_get_clean();
?>
<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>

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
                    <a class="nav-link active" aria-current="page" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="add_event.php">Add Event</a>
                </li>
            </ul>

            <div class="d-flex align-items-center">
                <a class="btn btn-sm btn-outline-secondary me-3" href="../index.php" title="Back to Site">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-1" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 1-.5.5H3.707l3.147 3.146a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L3.707 7.5H14.5A.5.5 0 0 1 15 8"/>
                    </svg>
                    Back to Site
                </a>
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
    <?= $dashboard_content ?>
</main>
<footer class="footer mt-auto py-3 bg-body">
    <div class="container text-center">
        <!-- copyright -->
        <span class="text-muted">&copy; 2025 City Events Dashboard</span>
    </div>
</footer>

<!-- needed in order to use svg icons -->
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
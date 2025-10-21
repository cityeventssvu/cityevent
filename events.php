<?php
require_once 'db.php'; 
include 'header.php';

# get filter values from GET request
$category_filter = $_GET['category'] ?? ''; 
$search_query = $_GET['search'] ?? '';
$date_filter = $_GET['date'] ?? '';

# check if any filter is active
$is_filtering = !empty($category_filter) || !empty($search_query) || !empty($date_filter);
# set page title based on filter status
$page_title = $is_filtering ? 'Filtered Event Results' : 'Explore All Events';

# initialize sql conditions and parameters
$sql_conditions = ['1=1'];
$sql_params = [];

# add category filter condition if category filter is not empty
if (!empty($category_filter)) {
    $sql_conditions[] = 'category = :category';
    $sql_params['category'] = $category_filter;
}

# add search filter condition if search query is not empty
if (!empty($search_query)) {
    $sql_conditions[] = '(title LIKE :search OR description LIKE :search)';
    $sql_params['search'] = '%' . $search_query . '%';
}

# add date filter condition if date filter is not empty
if (!empty($date_filter)) {
    $sql_conditions[] = 'DATE(event_date) = :date_filter';
    $sql_params['date_filter'] = $date_filter;
} 

# build sql where clause
$sql_where = implode(' AND ', $sql_conditions);
$sql = "SELECT id, title, category, event_date, location, description, image_path 
        FROM events 
        WHERE $sql_where 
        ORDER BY event_date ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($sql_params);
    $events = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_message = "A database error occurred while fetching events.";
    $events = [];
}

# truncate text to a specified length
function truncateText($text, $length = 100) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

# format event date and time
function formatEventDateTime($datetime) {
    return date('F j, Y \a\t g:i A', strtotime($datetime));
}

function getPlaceholderImageUrl($category) {
    $colors = [
        'Culture' => '1d4ed8/ffffff',
        'Sports' => 'ef4444/ffffff',
        'Music' => '8b5cf6/ffffff',
        'Family' => '10b981/ffffff',
        'Default' => '6b7280/ffffff'
    ];
    $code = $colors[$category] ?? $colors['Default'];
    return "https://placehold.co/400x250/$code?text=" . urlencode($category);
}

$available_categories = ['Culture', 'Sports', 'Music', 'Family', 'Other']; 

?>

<div class="container py-5">
    <h1 class="display-4 fw-bold mb-4 text-center"><?= $page_title ?></h1>

    <form method="GET" action="events.php" class="mb-5 p-4 bg-body-tertiary rounded shadow-sm">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label fw-bold">Search Title/Description</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h.001q.044.06.085.118l3.44 3.44a1 1 0 0 0 1.414-1.414l-3.44-3.44a1.2 1.2 0 0 0-.118-.085m-1.47 1.144A5.5 5.5 0 1 1 12.04 4A5.5 5.5 0 0 1 10.272 11.488z"/></svg>
                    </span>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Enter keywords..." value="<?= htmlspecialchars($search_query) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label fw-bold">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($available_categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" 
                                <?= $category_filter === $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date" class="form-label fw-bold">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($date_filter) ?>">
            </div>
            <div class="col-md-3">
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="events.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </div>
    </form>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger text-center" role="alert"><?= $error_message ?></div>
    <?php elseif (empty($events)): ?>
        <div class="alert alert-info text-center" role="alert">
            <h4 class="alert-heading">No Events Found</h4>
            <?php if ($is_filtering): ?>
                <p>We couldn't find any events matching your current search criteria. Try broadening your search or resetting the filters</p>
            <?php else: ?>
                <p>There are currently no events in the database. Check back later</p>
            <?php endif; ?>
            <hr>
            <a href="events.php" class="btn btn-sm btn-info">Show All Events</a>
        </div>
    <?php else: ?>
        <p class="text-muted text-center mb-4">Displaying <?= count($events) ?> event(s).</p>
        
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($events as $event): ?>
                <?php 
                    $image_src = $event['image_path'] ?: getPlaceholderImageUrl($event['category']);
                    $event_id = htmlspecialchars($event['id']);
                ?>
                <div class="col">
                    <div class="card h-100 shadow-lg border-0">
                        <img src="<?= htmlspecialchars($image_src) ?>" 
                             class="card-img-top object-fit-cover rounded-top" 
                             alt="<?= htmlspecialchars($event['title']) ?>" 
                             style="height: 200px;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-truncate"><?= htmlspecialchars($event['title']) ?></h5>
                            <div class="mb-2">
                                <span class="badge rounded-pill text-bg-primary me-2"><?= htmlspecialchars($event['category']) ?></span>
                            </div>
                            <p class="card-text small text-muted mb-2">
                                <?= formatEventDateTime($event['event_date']) ?>
                            </p>
                            <p class="card-text small text-muted mb-3">
                                <?= htmlspecialchars($event['location']) ?>
                            </p>
                            <p class="card-text mb-4 text-break"><?= htmlspecialchars(truncateText($event['description'], 100)) ?></p>
                            <a href="event.php?id=<?= $event_id ?>" class="btn btn-secondary mt-auto">View Details</a>                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php 
include 'footer.php';
?>

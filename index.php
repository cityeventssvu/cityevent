<?php

require_once 'db.php'; 

# fetch events data
try {
    $stmt_featured = $pdo->query("SELECT id, title, category, event_date, location, image_path FROM events ORDER BY RAND() LIMIT 3");
    $featured_events = $stmt_featured->fetchAll();
    // fetch latest events ordered by date descending and limit 6 events
    $stmt_latest = $pdo->query("SELECT id, title, category, event_date, image_path FROM events ORDER BY event_date DESC LIMIT 6");
    $latest_events = $stmt_latest->fetchAll();

} catch (PDOException $e) {
    $error_message = "Could not load events at this time. Please try again later.";
    $featured_events = [];
    $latest_events = [];
}

# format event date and time
function formatEventDateTime($datetime) {
    return date('F j, Y \a\t g:i A', strtotime($datetime));
}

# get placeholder image url
function getPlaceholderImageUrl($category) {
    $colors = [
        'Culture' => '1d4ed8/ffffff',
        'Sports' => 'ef4444/ffffff',
        'Music' => '8b5cf6/ffffff',
        'Family' => '10b981/ffffff',
        'Default' => '6b7280/ffffff'
    ];
    $code = $colors[$category] ?? $colors['Default'];
    return "https://placehold.co/400x250/$code?text=" . urlencode($category); // return placeholder image url
}

# include header
include 'header.php'; 
?>

<header class="position-relative overflow-hidden p-5 p-md-5 text-center bg-light" style="
    background-image: url('assets/img/header-bg.jpg');
    background-size: cover;
    background-position: center;
    color: white; 
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    
">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.4);"></div>

    <div class="col-md-5 p-lg-5 mx-auto my-5 position-relative">
        <h1 class="display-3 fw-bold text-shadow">City Events</h1>
        <p class="lead fw-normal text-shadow">Discover What’s Happening Around You</p>
        <a class="btn btn-primary mt-3 btn-lg shadow" href="#latest-events">Explore Events &darr;</a>
    </div>
</header>
<div class="container py-5">

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger text-center" role="alert">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <section class="mb-5">
        <h2 class="display-6 fw-bold text-center mb-4 pb-2 border-bottom">Featured Events This Week</h2>
        
        <?php if (!empty($featured_events)): ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($featured_events as $event): ?>
                    <?php 
                        $image_src = $event['image_path'] ?: getPlaceholderImageUrl($event['category']);
                        $card_class = $event['image_path'] ? 'bg-body' : 'bg-opacity-75';
                    ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0 <?= $card_class ?>">
                            <img src="<?= htmlspecialchars($image_src) ?>" 
                                 class="card-img-top object-fit-cover rounded-top" 
                                 alt="<?= htmlspecialchars($event['title']) ?>" 
                                 style="height: 200px;">
                            <div class="card-body d-flex flex-column">
                                <span class="badge bg-primary text-white mb-2"><?= htmlspecialchars($event['category']) ?></span>
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($event['title']) ?></h5>
                                <p class="card-text small text-muted mb-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock me-1" viewBox="0 0 16 16"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/></svg>
                                    <?= formatEventDateTime($event['event_date']) ?>
                                </p>
                                <p class="card-text small text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt-fill me-1" viewBox="0 0 16 16"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/></svg>
                                    <?= htmlspecialchars($event['location']) ?>
                                </p>
                                <a href="event.php?id=<?= htmlspecialchars($event['id']) ?>" class="btn btn-sm btn-outline-secondary mt-2">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No featured events found</p>
        <?php endif; ?>
    </section>

    <section class="my-5 py-4 bg-body-tertiary rounded shadow-sm">
        <h3 class="h4 text-center fw-bold mb-4">Quick Categories</h3>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <?php foreach (['Culture', 'Sports', 'Music', 'Family'] as $cat): ?>
                <a href="events.php?category=<?= urlencode($cat) ?>" class="btn btn-outline-primary btn-lg px-4 rounded-pill">
                    <?= htmlspecialchars($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="latest-events" class="pt-5">
        <h2 class="display-6 fw-bold text-center mb-4 pb-2 border-bottom">Latest Events</h2>
        
        <?php if (!empty($latest_events)): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
                <?php foreach ($latest_events as $event): ?>
                    <?php $image_src = $event['image_path'] ?: getPlaceholderImageUrl($event['category']); ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0">
                            <img src="<?= htmlspecialchars($image_src) ?>" 
                                 class="card-img-top object-fit-cover rounded-top" 
                                 alt="<?= htmlspecialchars($event['title']) ?>" 
                                 style="height: 180px;">
                            <div class="card-body pb-2">
                                <h6 class="card-title fw-bold text-truncate"><?= htmlspecialchars($event['title']) ?></h6>
                                <span class="badge bg-secondary mb-2"><?= htmlspecialchars($event['category']) ?></span>
                                <p class="card-text small text-muted mb-3">
                                    <?= formatEventDateTime($event['event_date']) ?>
                                </p>
                                <a href="event.php?id=<?= htmlspecialchars($event['id']) ?>" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
             <p class="text-center text-muted">No recent events to display</p>
        <?php endif; ?>
    </section>
</div>

<?php
# include footer
include 'footer.php'; 
?>
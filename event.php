<?php

require_once 'db.php'; 

# format event date and time
function formatEventDateTime($datetime) {
    return date('F j, Y \a\t g:i A', strtotime($datetime));
}

function getPlaceholderImageUrl($category) {
    # generate placeholder image URL for event banner
    $colors = [
        'Culture' => '1d4ed8/ffffff',
        'Sports' => 'ef4444/ffffff',
        'Music' => '8b5cf6/ffffff',
        'Family' => '10b981/ffffff',
        'Default' => '6b7280/ffffff'
    ];
    $code = $colors[$category] ?? $colors['Default'];
    return "https://placehold.co/1200x400/$code?text=" . urlencode("Event Banner: " . $category);
}

// generate URL slug from event title
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'event' : $text;
}

// generate ICS file content for calendar download
function generateICS($event) {
    $dtstart = gmdate('Ymd\THis\Z', strtotime($event['event_date']));
    # set default duration of 2 hours for calendar entry
    $dtend = gmdate('Ymd\THis\Z', strtotime($event['event_date']) + (3600 * 2)); 

    $ics_content = "BEGIN:VCALENDAR\r\n";
    $ics_content .= "VERSION:2.0\r\n";
    $ics_content .= "PRODID:-//City Events Hub//NONSGML v1.0//EN\r\n";
    $ics_content .= "BEGIN:VEVENT\r\n";
    $ics_content .= "UID:" . md5($event['id'] . time()) . "@cityevents.com\r\n";
    $ics_content .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
    $ics_content .= "DTSTART:" . $dtstart . "\r\n";
    $ics_content .= "DTEND:" . $dtend . "\r\n";
    $ics_content .= "SUMMARY:" . $event['title'] . "\r\n";
    $ics_content .= "LOCATION:" . $event['location'] . "\r\n";
    $description = str_replace(["\r", "\n", ","], ["\\r", "\\n", "\\,"], $event['description']);
    $ics_content .= "DESCRIPTION:" . $description . "\r\n";
    $ics_content .= "END:VEVENT\r\n";
    $ics_content .= "END:VCALENDAR\r\n";

    return $ics_content;
}


// fetch main event details
$event_id = $_GET['id'] ?? null;
$main_event = null;
$error_message = '';


if ($event_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->execute(['id' => $event_id]);
        $main_event = $stmt->fetch();
    } catch (PDOException $e) {
        $error_message = "Database error: Could not load event details.";
    }
}

if (!$main_event) {
    # if event not found or ID is missing
    $error_message = $error_message ?: "Error: Event ID missing or event not found.";
}

// ICS download handler must run before any HTML output
if (isset($_GET['action']) && $_GET['action'] === 'ics' && $main_event) {
    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . slugify($main_event['title']) . '.ics"');
    echo generateICS($main_event);
    exit;
}

# fetch related events
$related_events = [];
if ($main_event) {
    try {
        $stmt_related = $pdo->prepare("
            SELECT id, title, category, event_date, image_path 
            FROM events 
            WHERE category = :category AND id != :id 
            ORDER BY event_date ASC 
            LIMIT 3
        ");
        $stmt_related->execute([
            'category' => $main_event['category'],
            'id' => $main_event['id']
        ]);
        $related_events = $stmt_related->fetchAll();
    } catch (PDOException $e) {
        $error_message = "Database error";
    }
}

// Include the base layout files
include 'header.php';
?>

<div class="container py-5">
    <?php if ($error_message): ?>
        <div class="alert alert-danger text-center" role="alert">
            <h4 class="alert-heading">Event Not Available</h4>
            <p><?= htmlspecialchars($error_message) ?></p>
            <hr>
            <a href="events.php" class="btn btn-danger">Go Back to All Events</a>
        </div>
    <?php else: ?>
        
        <?php 
            $image_src = $main_event['image_path'] ?: getPlaceholderImageUrl($main_event['category']);
        ?>
        <div class="card bg-dark text-white rounded shadow-lg mb-4 overflow-hidden" style="max-height: 400px;">
             <img src="<?= htmlspecialchars($image_src) ?>" class="card-img object-fit-cover w-100" alt="<?= htmlspecialchars($main_event['title']) ?>" style="height: 400px;">
        </div>

        <div class="row">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3"><?= htmlspecialchars($main_event['title']) ?></h1>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="badge bg-secondary fs-6 p-2 rounded-pill">
                        <?= formatEventDateTime($main_event['event_date']) ?>
                    </span>
                    <span class="badge bg-secondary fs-6 p-2 rounded-pill">
                        <?= htmlspecialchars($main_event['location']) ?>
                    </span>
                    <span class="badge bg-primary fs-6 p-2 rounded-pill"><?= htmlspecialchars($main_event['category']) ?></span>
                </div>

                <hr>

                <section class="mt-4">
                    <h3 class="h4 fw-bold mb-3">About This Event</h3>
                    <p class="lead text-break"><?= nl2br(htmlspecialchars($main_event['description'])) ?></p>
                </section>
                
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm p-4 sticky-top" style="top: 20px;">
                    <h5 class="card-title fw-bold border-bottom pb-2 mb-3">Event Actions</h5>
                    
                    <a href="event.php?id=<?= htmlspecialchars($main_event['id']) ?>&action=ics" 
                       class="btn btn-success btn-lg mb-3">
                        Add to Calendar (.ics)
                    </a>
                    
                    <button id="share-button" class="btn btn-outline-info btn-lg">
                        Share Event
                    </button>
                    <div id="share-message" class="text-center small mt-2 text-success" style="height: 20px;"></div>
                </div>
            </div>
        </div>

        <?php if (!empty($related_events)): ?>
            <section class="mt-5 pt-4 border-top">
                <h3 class="h4 fw-bold mb-4">More <?= htmlspecialchars($main_event['category']) ?> Events You Might Like</h3>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach ($related_events as $event): ?>
                        <?php 
                            $related_image_src = $event['image_path'] ?: getPlaceholderImageUrl($event['category']);
                        ?>
                        <div class="col">
                             <div class="card h-100 shadow-sm border-0">
                                <img src="<?= htmlspecialchars($related_image_src) ?>" 
                                     class="card-img-top object-fit-cover rounded-top" 
                                     alt="<?= htmlspecialchars($event['title']) ?>" 
                                     style="height: 180px;">
                                <div class="card-body d-flex flex-column pb-2">
                                    <h6 class="card-title fw-bold text-truncate"><?= htmlspecialchars($event['title']) ?></h6>
                                    <span class="badge bg-secondary mb-2"><?= formatEventDateTime($event['event_date']) ?></span>
                                    <a href="event.php?id=<?= htmlspecialchars($event['id']) ?>" class="btn btn-sm btn-outline-secondary mt-auto">View Event</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

    <?php endif; ?>
</div>

<script>
    // share event link to clipboard
    document.addEventListener('DOMContentLoaded', function() {
        const shareButton = document.getElementById('share-button');
        const shareMessage = document.getElementById('share-message');

        if (shareButton) {
            shareButton.addEventListener('click', function() {
                const eventUrl = window.location.href;
                
                // remove the actionics parameter if present before sharing
                const cleanUrl = eventUrl.split('&action=ics')[0];

                // use the clipboard API if available, otherwise fallback
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(cleanUrl).then(function() {
                        shareMessage.textContent = 'Link copied to clipboard!';
                        //clear the message after 3 seconds
                        setTimeout(() => shareMessage.textContent = '', 3000);
                    }).catch(function(err) {
                        shareMessage.textContent = 'Could not copy link.';
                    });
                } else {
                    // fallback using execCommand 
                    const tempInput = document.createElement('input');
                    tempInput.value = cleanUrl;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    try {
                        // copy the link to the clipboard
                        document.execCommand('copy');
                        shareMessage.textContent = 'Link copied to clipboard!';
                        setTimeout(() => shareMessage.textContent = '', 3000);
                    } catch (err) {
                        shareMessage.textContent = 'Error copying link.';
                    }
                    document.body.removeChild(tempInput);
                }
            });
        }
    });
</script>

<?php 
// Include the shared footer file
include 'footer.php';
?>

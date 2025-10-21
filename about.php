<?php
require_once __DIR__ . '/header.php';
?>

<section class="py-5 bg-body-secondary border-bottom">
  <div class="container">
    <h1 class="display-5 fw-bold mb-2">About City Events</h1>
    <p class="lead mb-0">Discover, share, and experience the best events around you</p>
  </div>
  
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-6">
        <h2 class="h3 mb-3">Our Mission</h2>
        <p class="mb-0">We connect people with local happenings by making it effortless to explore events that match their interests, schedule, and neighborhood</p>
      </div>
      <div class="col-lg-6">
        <h2 class="h3 mb-3">Our Vision</h2>
        <p class="mb-0">A vibrant, inclusive community where everyone can easily discover and attend meaningful events, fostering connection and culture in every city</p>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-body-secondary border-top border-bottom">
  <div class="container">
    <h2 class="h3 mb-4">Our Team</h2>
    <?php
    // team data
      $team = [
        ['name' => 'Ali Deeb', 'role' => 'Product Director', 'desc' => 'Guides product strategy to make event discovery simple and joyful'],
        ['name' => 'Sara Algeroude', 'role' => 'Community Manager', 'desc' => 'Builds partnerships with organizers and supports our user community'],
        ['name' => 'Razan ALOmari', 'role' => 'Full Stack Developer', 'desc' => 'Crafts fast, reliable features across the stack'],
        ['name' => 'Raghad Dogha', 'role' => 'User Experience Designer', 'desc' => 'Designs inclusive, accessible experiences for all users'],
        ['name' => 'Asmaa Alhamoi', 'role' => 'Data Analyst', 'desc' => 'Turns insights into better recommendations and smarter search'],
      ];
    ?>
    <div class="row g-4">
      <?php foreach ($team as $member): ?>
        <div class="col-sm-6 col-lg-4">
          <div class="card h-100 shadow-sm bg-body border-0">
            <div class="card-body">
              <h3 class="h5 mb-1"><?php echo htmlspecialchars($member['name']); ?></h3>
              <p class="text-primary mb-2"><?php echo htmlspecialchars($member['role']); ?></p>
              <p class="mb-0"><?php echo htmlspecialchars($member['desc']); ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <h2 class="h3 mb-3">Event Policies</h2>
    <ul class="list-group list-group-flush bg-body rounded-3 overflow-hidden">
      <li class="list-group-item bg-body">Event details are accurate and up to date</li>
      <li class="list-group-item bg-body">Events should be respectful and inclusive</li>
      <li class="list-group-item bg-body">We may review listings that violate our guidelines</li>
      <li class="list-group-item bg-body">Ticket terms are managed by organizers</li>
      <li class="list-group-item bg-body">We protect user data</li>
    </ul>
  </div>
</section>

<?php
require_once __DIR__ . '/footer.php';
?>

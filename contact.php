<?php
require_once __DIR__ . '/header.php';
?>

<div class="container py-5 contact-page">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-4">Contact Us</h1>

            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'sent'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Your message has been sent successfully
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        There was an error sending your message. Please check the form and try again
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="card shadow-sm bg-body-tertiary border-0">
                <div class="card-body">
                    <form id="contactForm" action="contact_process.php" method="post" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required minlength="2" placeholder="Your name">
                            <div class="invalid-feedback">Please enter your name (at least 2 characters)</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="you@example.com">
                            <div class="invalid-feedback">Please enter a valid email address</div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required minlength="5" placeholder="How can we help?"></textarea>
                            <div class="invalid-feedback">Please enter a message (at least 5 characters)</div>
                        </div>

                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>

            <div class="mt-4">
                <div class="card shadow-sm bg-body-tertiary border-0">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Other ways to reach us</h2>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><strong>Email:</strong> <a href="mailto:hello@cityevents.com">hello@cityevents.com</a></li>
                            <li><strong>Phone:</strong> <a href="tel:+963999999999">+963 999999999</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
require_once __DIR__ . '/footer.php';
?>

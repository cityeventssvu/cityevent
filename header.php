<?php
session_start();
// header.php public site header and navigation bar
$currentPage = basename($_SERVER['PHP_SELF']);
$currentPage = preg_replace('/\.php$/', '', $currentPage);
?>
<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>City Events Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="assets/css/styles.css" rel="stylesheet">
    <link rel="icon" href="assets/img/fav.png" type="image/png">

    <style>
        body {
            transition: background-color 0.3s, color 0.3s;
        }

        .navbar {
            border-bottom: 1px solid var(--bs-border-color-translucent);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .text-shadow {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .card-img-top {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .nav-link.active,
        .nav-link:hover {
            font-weight: 500;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-body">

    <nav class="navbar navbar-expand-lg bg-body-tertiary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">CityEvents</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item <?= $currentPage === 'index' ? 'active' : '' ?>">
                        <a class="nav-link <?= $currentPage === 'index' ? 'fw-bold' : '' ?>" aria-current="page" href="index.php">Home</a>
                    </li>
                    <li class="nav-item <?= $currentPage === 'events' ? 'active' : '' ?>">
                        <a class="nav-link <?= $currentPage === 'events' ? 'fw-bold' : '' ?>" href="events.php">All Events</a>
                    </li>
                    <li class="nav-item <?= $currentPage === 'about' ? 'active' : '' ?>">
                        <a class="nav-link <?= $currentPage === 'about' ? 'fw-bold' : '' ?>" href="about.php">About</a>
                    </li>
                    <li class="nav-item <?= $currentPage === 'contact' ? 'active' : '' ?>">
                        <a class="nav-link <?= $currentPage === 'contact' ? 'fw-bold' : '' ?>" href="contact.php">Contact Us</a>
                    </li>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-primary" href="admin/login.php">Log in</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-primary" href="admin/login.php">Admin</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="d-flex align-items-center">
                    <button id="theme-toggle" class="btn btn-sm btn-outline-primary" title="Toggle theme">
                        <span class="fs-6" aria-hidden="true">☀</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    <main class="flex-shrink-0">
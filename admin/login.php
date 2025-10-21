<?php
session_start();

$alert_message = '';
$error_message = '';
$success_message = '';

// show message from $_GET
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
    
    if ($msg === 'logout') {
        $alert_message = '<div class="alert alert-success" role="alert">
                            You have been logged out successfully.
                          </div>';
    } elseif ($msg === 'registered') {
        $success_message = '<div class="alert alert-success" role="alert">
                            Registration successful! Please log in with your credentials.
                          </div>';
    }
}

if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../db.php'; 

// authenticate user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
    
    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error_message = 'Please enter both username and password.';
        } else {
            try {
                // get user
                $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = :username");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch();

                // login check
                if ($user && password_verify($password, $user['password_hash'])) {
                    $_SESSION['admin'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error_message = 'Invalid username or password.';
                }
            } catch (PDOException $e) {
                error_log('Database Error: ' . $e->getMessage());
                $error_message = 'A server error occurred';
            }
        }
    } elseif ($action === 'signup') {
        // signup
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $signup_errors = [];

        // username check
        if (empty($username)) {
            $signup_errors['username'] = 'Username is required.';
        } elseif (strlen($username) < 3) {
            $signup_errors['username'] = 'Username must be at least 3 characters.';
        }

        // email check
        if (empty($email)) {
            $signup_errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $signup_errors['email'] = 'Please enter a valid email address.';
        }

        // password check
        if (empty($password)) {
            $signup_errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 5) {
            $signup_errors['password'] = 'Password must be at least 5 characters.';
        }

        // confirm password check
        if ($password !== $confirm_password) {
            $signup_errors['confirm_password'] = 'Passwords do not match.';
        }

        if (empty($signup_errors)) {
            try {
                // check if username or email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
                $stmt->execute(['username' => $username, 'email' => $email]);
                
                if ($stmt->fetch()) {
                    $error_message = 'Username or email already exists.';
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, created_at) VALUES (:username, :email, :password_hash, NOW())");
                    $stmt->execute([
                        'username' => $username,
                        'email' => $email,
                        'password_hash' => $password_hash
                    ]);
                    
                    // redirect to login page with success message
                    header('Location: login.php?msg=registered');
                    exit;
                }
            } catch (PDOException $e) {
                error_log('Database Error: ' . $e->getMessage());
                $error_message = 'Registration failed. Please try again.';
            }
        } else {
            $error_message = implode('<br>', $signup_errors);
        }
    }
}
?>

<!-- login/signup form using bootstrap -->
<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login/Signup</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="../assets/css/styles.css" rel="stylesheet">
    <link rel="icon" href="../assets/img/fav.png" type="image/png">

    <style>
        body {
            transition: background-color 0.3s, color 0.3s;
        }
        .form-container {
            transition: all 0.3s ease-in-out;
        }
        .form-hidden {
            display: none;
        }
        .toggle-buttons {
            margin-bottom: 2rem;
        }
        .back-home-container {
            margin-top: 1.5rem;
        }
    </style>
</head>
<body class="bg-body-tertiary">

<?php echo $alert_message; ?>
<?php echo $success_message; ?>

<div class="vh-100 d-flex align-items-center justify-content-center">
    <div class="card p-4 shadow-lg" style="width: 100%; max-width: 450px;">
        <div class="card-body">
            <h1 class="card-title text-center mb-4">City Events Admin</h1>

            <div class="toggle-buttons text-center">
                <div class="btn-group" role="group" aria-label="Login/Signup toggle">
                    <button type="button" class="btn btn-outline-primary active" id="login-toggle">Sign In</button>
                    <button type="button" class="btn btn-outline-primary" id="signup-toggle">Sign Up</button>
                </div>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="login-form" class="form-container">
                <input type="hidden" name="action" value="login">
                
                <div class="mb-3">
                    <label for="login-username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="login-username" name="username" required 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="mb-4">
                    <label for="login-password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="login-password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    Sign In
                </button>
            </form>

            <form method="POST" id="signup-form" class="form-container form-hidden">
                <input type="hidden" name="action" value="signup">
                
                <div class="mb-3">
                    <label for="signup-username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="signup-username" name="username" required 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="signup-email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="signup-email" name="email" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="signup-password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="signup-password" name="password" required>
                </div>

                <div class="mb-4">
                    <label for="signup-confirm-password" class="form-label">Confirm password</label>
                    <input type="password" class="form-control" id="signup-confirm-password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-success w-100 mb-3">
                    Sign Up
                </button>
            </form>
            
            <div class="text-center mt-3">
                <button id="theme-toggle" class="btn btn-sm btn-outline-primary" title="Toggle theme">
                    <svg class="bi" width="16" height="16" fill="currentColor">
                        <use href="#sun-fill"></use>
                    </svg>
                </button>
            </div>

            <div class="text-center back-home-container">
                <a href="../index.php" class="btn btn-secondary w-100">Back to Home</a>
            </div>
        </div>
    </div>
</div>


<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
    <symbol id="sun-fill" viewBox="0 0 16 16">
        <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
    </symbol>
    <symbol id="moon-stars-fill" viewBox="0 0 16 16">
        <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277A.768.768 0 0 1 15.858 13a7.208 7.208 0 0 1-7.962-7.962 7.208 7.208 0 0 0 4.218-3.535.79.79 0 0 1 .858-.08zm.287 4.197a.768.768 0 0 0-.256-.051A6.993 6.993 0 0 1 5.166 8.3c0 1.05.158 2.07.458 3.018A.768.768 0 0 0 6 12.046a.795.795 0 0 1-.758 1.157 8.163 8.163 0 0 0 4.153-2.022.774.774 0 0 1 .288-.231 7.272 7.272 0 0 0 2.217-5.06A.768.768 0 0 0 12.046 6.002a.795.795 0 0 1-1.157-.758A8.163 8.163 0 0 0 6.73 3.824a.774.774 0 0 1-.23.288z"/>
    </symbol>
</svg>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="../assets/js/main.js"></script>
<script src="theme_toggle.js"></script>

<script>
    (function () {
        'use strict'

        const loginToggle = document.getElementById('login-toggle');
        const signupToggle = document.getElementById('signup-toggle');
        const loginForm = document.getElementById('login-form');
        const signupForm = document.getElementById('signup-form');

        function showLogin() {
            loginToggle.classList.add('active');
            signupToggle.classList.remove('active');
            loginForm.classList.remove('form-hidden');
            signupForm.classList.add('form-hidden');
        }

        function showSignup() {
            signupToggle.classList.add('active');
            loginToggle.classList.remove('active');
            signupForm.classList.remove('form-hidden');
            loginForm.classList.add('form-hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (loginToggle && signupToggle) {
                loginToggle.addEventListener('click', showLogin);
                signupToggle.addEventListener('click', showSignup);
            }
        });
    })();
    </script>
</body>
</html>
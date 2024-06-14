<?php
session_start();
include 'config.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Registration
if ($action == 'register' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Registration successful! Please log in.";
        header("Location: index.php?action=login");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Login
if ($action == 'login' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['message'] = "Login successful! Welcome back.";
        header("Location: index.php");
    } else {
        echo "Invalid username or password";
    }

    $stmt->close();
}

// Logout
if ($action == 'logout') {
    session_unset();
    session_destroy();
    session_start(); // Restart session to set the message
    $_SESSION['message'] = "You have been logged out.";
    header("Location: index.php?action=login");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header">
        <h1>OurLogo</h1>
        <ul class="nav">
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php?action=about">About</a></li>
            <li><a href="index.php?action=portfolio">Portfolio</a></li>

            <?php if (isset($_SESSION['username'])): ?>
                <li><a href="index.php?action=logout">Logout</a></li>
            <?php else: ?>
                <li><a href="index.php?action=login">Login</a></li>
                <li><a href="index.php?action=register">Register</a></li>
            <?php endif; ?>

        </ul>
    </div>

    <div class="content">
        <?php
        if ($action == 'register') {
            include 'register_form.php';
        } elseif ($action == 'login') {
            include 'login_form.php';
        } elseif ($action == 'about') {
            include 'about.php';
        } elseif ($action == 'portfolio') {
            include 'portfolio.php';
        } else {
            if (isset($_SESSION['username'])) {
                echo "<div class='hero'>
                        <div class='hero-inner'>
                            <h1>Welcome, " . htmlspecialchars($_SESSION['username']) . "!</h1>
                            <p>You have successfully logged in.</p>
                        </div>
                      </div>";
            } else {
                echo "<div class='hero'>
                        <div class='hero-inner'>
                            <h1>Welcome To Our Site!</h1>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Fugiat nesciunt accusantium tempora voluptatem velit enim quaerat sapiente sed, repellat temporibus eaque dignissimos, saepe ab recusandae incidunt fuga veniam vero vitae!</p>
                            <a href='#' class='button'>Learn More</a>
                        </div>
                      </div>";
            }
        }
        ?>
    </div>

    <div class="footer">
        <p>&copy; 2024 Company</p>
    </div>

    <script>
        // Check for session message
        <?php if (isset($_SESSION['message'])): ?>
        alert('<?php echo $_SESSION['message']; unset($_SESSION['message']); ?>');
        <?php endif; ?>
    </script>
</body>
</html>


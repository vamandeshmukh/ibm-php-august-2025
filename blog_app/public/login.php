<?php
require_once __DIR__ . '/../src/Services/AuthService.php';

session_start();
$authService = new App\Services\AuthService();

if ($authService->isLoggedIn()) {
    header('Location: profile.php');
    error_log("Redirecting to profile...");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    echo "<script>console.log(" . json_encode($email) . ");</script>";

    if (empty($email) || empty($password)) {
        $error = 'Both email and password are required';
    } else {
        $result = $authService->login($email, $password);

        if ($result['success']) {
            header('Location: profile.php');
            echo "<script>console.log(" . json_encode($result) . ");</script>";
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>


<?php include __DIR__ . '/../templates/includes/header.php'; ?>

<main>
    <h1>Login</h1>

    <?php if ($error): ?>
        <div><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
        </div>

        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
</main>

<?php include __DIR__ . '/../templates/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../src/Services/AuthService.php';

session_start();
$authService = new App\Services\AuthService();

if (!$authService->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $authService->getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    if (empty($username) || empty($email)) {
        $error = 'Username and email are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $result = $authService->updateProfile($user->getId(), $username, $email, $bio);

        if ($result['success']) {
            $success = $result['message'];
            $user = $authService->getCurrentUser();
        } else {
            $error = $result['message'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All password fields are required';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } else {
        $result = $authService->changePassword($user->getId(), $current_password, $new_password);

        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>

<?php include __DIR__ . '/../templates/includes/header.php'; ?>

<main>
    <h1>Your Profile</h1>

    <?php if ($error): ?>
        <div style="color: red; padding: 10px; border: 1px solid red; margin: 10px 0;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="color: green; padding: 10px; border: 1px solid green; margin: 10px 0;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <section>
        <h2>Profile Information</h2>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user->getUsername()); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user->getEmail()); ?></p>
        <p><strong>Bio:</strong> <?php echo htmlspecialchars($user->getBio() ?? 'No bio yet'); ?></p>
        <p><strong>Member since:</strong> <?php echo date('M j, Y', strtotime($user->getCreatedAt())); ?></p>
    </section>

    <section>
        <h2>Update Profile</h2>
        <form method="POST" action="profile.php">
            <input type="hidden" name="update_profile" value="1">

            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username"
                    value="<?php echo htmlspecialchars($user->getUsername()); ?>" required>
            </div>

            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user->getEmail()); ?>"
                    required>
            </div>

            <div>
                <label for="bio">Bio:</label>
                <textarea id="bio" name="bio" rows="4" cols="50"
                    placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user->getBio() ?? ''); ?></textarea>
            </div>

            <button type="submit">Update Profile</button>
        </form>
    </section>

    <section>
        <h2>Change Password</h2>
        <form method="POST" action="profile.php">
            <input type="hidden" name="change_password" value="1">

            <div>
                <label for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>

            <div>
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>

            <div>
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit">Change Password</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../templates/includes/footer.php'; ?>
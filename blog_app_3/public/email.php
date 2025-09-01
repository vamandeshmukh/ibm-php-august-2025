<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

session_start();
$authService = new App\Services\AuthService();

if (!$authService->isLoggedIn()) {
    header('Location: login.php');
    exit;
}


// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim($_POST['to'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');

    // Gmail App Password read from file
    $appPassword = file_get_contents("D:/Projects/data/gmail_app_password.txt");

    if (empty($to) || empty($subject) || empty($body)) {
        $error = 'All fields (To, Subject, Body) are required';
    } else {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['GMAIL_USER'];
            $mail->Password = $appPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($_ENV['GMAIL_USER'], $_ENV['GMAIL_NAME'] ?? 'App Mailer');
            $mail->addAddress($to);

            // Attachment if uploaded
            if (!empty($_FILES['attachment']['tmp_name'])) {
                $mail->addAttachment($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']);
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = nl2br($body);
            $mail->AltBody = $body;

            $mail->send();
            $success = 'Email has been sent successfully!';
        } catch (Exception $e) {
            $error = "Email could not be sent. Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<?php include __DIR__ . '/../templates/includes/header.php'; ?>

<main>
    <h1>Send Email</h1>

    <?php if ($success): ?>
        <div style="color: green;"><?php echo htmlspecialchars($success); ?></div>
    <?php elseif ($error): ?>
        <div style="color: red;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="email.php" enctype="multipart/form-data">
        <div>
            <label for="from">From:</label>
            <input type="email" id="from" name="from" value="<?php echo htmlspecialchars($_ENV['GMAIL_USER']); ?>"
                readonly>
        </div>

        <div>
            <label for="to">To:</label>
            <input type="email" id="to" name="to" required>
        </div>

        <div>
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required>
        </div>

        <div>
            <label for="body">Body:</label><br>
            <textarea id="body" name="body" rows="6" cols="50" required></textarea>
        </div>

        <div>
            <label for="attachment">Attachment:</label>
            <input type="file" id="attachment" name="attachment">
        </div>

        <button type="submit">Send Email</button>
    </form>
</main>

<?php include __DIR__ . '/../templates/includes/footer.php'; ?>
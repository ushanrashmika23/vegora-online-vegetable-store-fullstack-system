<?php
session_start();
require_once __DIR__ . '/config/db.php';

$errors = [];
$success = '';

const DEFAULT_SUBJECT = 'Vegora Email Test';
const DEFAULT_MESSAGE = "Hello,\n\nThis is a test email from Vegora.\n\nRegards,\nVegora Team";
const DEFAULT_FROM_EMAIL = 'noreply@vegora.local';
const DEFAULT_SMTP_HOST = 'smtp.gmail.com';
const DEFAULT_SMTP_PORT = 587;

function inputValue($key, $default = '')
{
  return trim((string)($_POST[$key] ?? $default));
}

function loadUsers(PDO $pdo, array &$errors)
{
  try {
    $stmt = $pdo->query("SELECT id, name, email FROM users ORDER BY name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Throwable $e) {
    $errors[] = 'Could not load users list from database.';
    return [];
  }
}

function smtpReadLine($socket)
{
  $data = '';
  while (($line = fgets($socket, 515)) !== false) {
    $data .= $line;
    if (preg_match('/^\d{3} /', $line)) {
      break;
    }
    }
  return $data;
}

function smtpExpect($socket, array $okCodes, &$lastResponse)
{
  $lastResponse = smtpReadLine($socket);
  if (!preg_match('/^(\d{3})/', $lastResponse, $m)) {
    return false;
  }
  return in_array((int)$m[1], $okCodes, true);
}

function smtpCommand($socket, $command, array $okCodes, &$lastResponse)
{
  fwrite($socket, $command . "\r\n");
  return smtpExpect($socket, $okCodes, $lastResponse);
}

function sendViaGmailSmtp($host, $port, $username, $appPassword, $fromEmail, $toEmail, $subject, $body, &$error)
{
  $error = '';

  $socket = @stream_socket_client('tcp://' . $host . ':' . $port, $errno, $errstr, 20);
  if (!$socket) {
    $error = 'Connection failed: ' . $errstr . ' (' . $errno . ')';
    return false;
  }

  stream_set_timeout($socket, 20);
  $resp = '';

  if (!smtpExpect($socket, [220], $resp)) {
    $error = 'SMTP greeting failed: ' . trim($resp);
    fclose($socket);
    return false;
  }
  if (!smtpCommand($socket, 'EHLO localhost', [250], $resp)) {
    $error = 'EHLO failed: ' . trim($resp);
    fclose($socket);
    return false;
  }
  if (!smtpCommand($socket, 'STARTTLS', [220], $resp)) {
    $error = 'STARTTLS failed: ' . trim($resp);
    fclose($socket);
    return false;
  }
  if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
    $error = 'TLS handshake failed.';
    fclose($socket);
    return false;
  }
  if (!smtpCommand($socket, 'EHLO localhost', [250], $resp)) {
    $error = 'EHLO after TLS failed: ' . trim($resp);
    fclose($socket);
    return false;
  }
  if (!smtpCommand($socket, 'AUTH LOGIN', [334], $resp)) {
    $error = 'AUTH LOGIN failed: ' . trim($resp);
    fclose($socket);
    return false;
  }
  if (!smtpCommand($socket, base64_encode($username), [334], $resp)) {
    $error = 'SMTP username rejected: ' . trim($resp);
    fclose($socket);
    return false;
  }
  if (!smtpCommand($socket, base64_encode($appPassword), [235], $resp)) {
    $error = 'SMTP app password rejected: ' . trim($resp);
    fclose($socket);
    return false;
  }
  if (!smtpCommand($socket, 'MAIL FROM:<' . $fromEmail . '>', [250], $resp)) {
    $error = 'MAIL FROM failed: ' . trim($resp);
    fclose($socket);
    return false;
  }
  if (!smtpCommand($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251], $resp)) {
    $error = 'RCPT TO failed: ' . trim($resp);
    fclose($socket);
    return false;
  }
  if (!smtpCommand($socket, 'DATA', [354], $resp)) {
    $error = 'DATA command failed: ' . trim($resp);
    fclose($socket);
    return false;
  }

  $headers = [
    'From: Vegora <' . $fromEmail . '>',
    'To: <' . $toEmail . '>',
    'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit'
  ];

  $safeBody = str_replace(["\r\n.", "\n.", "\r."], ["\r\n..", "\n..", "\r.."], $body);
  $messageData = implode("\r\n", $headers) . "\r\n\r\n" . $safeBody . "\r\n.";
  fwrite($socket, $messageData . "\r\n");

  if (!smtpExpect($socket, [250], $resp)) {
    $error = 'Message body rejected: ' . trim($resp);
    fclose($socket);
    return false;
  }

  smtpCommand($socket, 'QUIT', [221], $resp);
  fclose($socket);
  return true;
}

function resolveRecipientEmail(PDO $pdo, $selectedUserId, $typedEmail, array &$errors)
{
  if ($selectedUserId <= 0) {
    return $typedEmail;
  }

  try {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $selectedUserId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && !empty($user['email'])) {
      return (string)$user['email'];
    }
  } catch (Throwable $e) {
    $errors[] = 'Could not fetch selected user email.';
  }

  return $typedEmail;
}

function validateForm($data)
{
  $errors = [];

  if ($data['toEmail'] === '' || !filter_var($data['toEmail'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter a valid recipient email or select a user.';
  }
  if ($data['fromEmail'] === '' || !filter_var($data['fromEmail'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter a valid sender email.';
  }
  if ($data['smtpUser'] === '' || !filter_var($data['smtpUser'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter your Gmail SMTP username (your Gmail address).';
  }
  if ($data['smtpAppPassword'] === '') {
    $errors[] = 'Enter your Google App Password.';
  }
  if ($data['subject'] === '') {
    $errors[] = 'Subject is required.';
  }
  if ($data['message'] === '') {
    $errors[] = 'Message is required.';
  }
  if ($data['smtpHost'] === '') {
    $errors[] = 'SMTP host is required.';
  }
  if ($data['smtpPort'] <= 0) {
    $errors[] = 'SMTP port must be greater than 0.';
  }

  return $errors;
}

$users = loadUsers($pdo, $errors);

$selectedUserId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : (int)($_GET['user_id'] ?? 0);
$toEmail = inputValue('to_email', (string)($_GET['to'] ?? ''));
$subject = inputValue('subject', DEFAULT_SUBJECT);
$message = inputValue('message', DEFAULT_MESSAGE);
$fromEmail = inputValue('from_email', DEFAULT_FROM_EMAIL);
$smtpHost = inputValue('smtp_host', DEFAULT_SMTP_HOST);
$smtpPort = (int)($_POST['smtp_port'] ?? DEFAULT_SMTP_PORT);
$smtpUser = inputValue('smtp_user', '');
$smtpAppPassword = trim((string)($_POST['smtp_app_password'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $toEmail = resolveRecipientEmail($pdo, $selectedUserId, $toEmail, $errors);

  $payload = [
    'toEmail' => $toEmail,
    'fromEmail' => $fromEmail,
    'smtpUser' => $smtpUser,
    'smtpAppPassword' => $smtpAppPassword,
    'subject' => $subject,
    'message' => $message,
    'smtpHost' => $smtpHost,
    'smtpPort' => $smtpPort
  ];

  $errors = array_merge($errors, validateForm($payload));

    if (empty($errors)) {
        $smtpError = '';
        $sent = sendViaGmailSmtp($smtpHost, $smtpPort, $smtpUser, $smtpAppPassword, $fromEmail, $toEmail, $subject, $message, $smtpError);

        if ($sent) {
            $success = 'Test email sent successfully to ' . $toEmail . '.';
        } else {
          $errors[] = 'SMTP send failed: ' . ($smtpError !== '' ? $smtpError : 'Unknown SMTP error.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test Mail - Vegora</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .mail-card { max-width: 760px; margin: 3rem auto; }
  </style>
</head>
<body>
  <div class="container mail-card">
    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-body p-4 p-md-5">
        <h1 class="h3 mb-2">Send Test Email</h1>
        <p class="text-muted mb-4">Use this page to test outgoing email from your local Vegora setup.</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <?php foreach ($errors as $err): ?>
              <div><?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="testMail.php" class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">SMTP Host</label>
            <input type="text" name="smtp_host" class="form-control" value="<?php echo htmlspecialchars($smtpHost); ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">SMTP Port</label>
            <input type="number" name="smtp_port" class="form-control" value="<?php echo (int)$smtpPort; ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Gmail Username</label>
            <input type="email" name="smtp_user" class="form-control" value="<?php echo htmlspecialchars($smtpUser); ?>" placeholder="you@gmail.com" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Google App Password</label>
            <input type="password" name="smtp_app_password" class="form-control" value="" placeholder="16-character app password" autocomplete="new-password" required>
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold">Pick User (optional)</label>
            <select name="user_id" class="form-select">
              <option value="0">-- Select user --</option>
              <?php foreach ($users as $u): ?>
                <option value="<?php echo (int)$u['id']; ?>" <?php echo $selectedUserId === (int)$u['id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($u['name'] . ' (' . $u['email'] . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">If selected, user email overrides manual recipient email.</div>
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold">Recipient Email</label>
            <input type="email" name="to_email" class="form-control" value="<?php echo htmlspecialchars($toEmail); ?>" placeholder="user@example.com">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">From Email</label>
            <input type="email" name="from_email" class="form-control" value="<?php echo htmlspecialchars($fromEmail); ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Subject</label>
            <input type="text" name="subject" class="form-control" value="<?php echo htmlspecialchars($subject); ?>" required>
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold">Message</label>
            <textarea name="message" rows="8" class="form-control" required><?php echo htmlspecialchars($message); ?></textarea>
          </div>

          <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-success px-4">Send Test Mail</button>
            <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
          </div>
        </form>

        <hr class="my-4">

        <div class="small text-muted">
          <div><strong>Gmail setup:</strong> Use <code>smtp.gmail.com</code> on <code>587</code>, enable 2-Step Verification, then create and use an App Password.</div>
          <div>For best delivery, use the same Gmail in both <code>Gmail Username</code> and <code>From Email</code>.</div>
          <div>Quick open link with prefilled email: <code>testMail.php?to=user@example.com</code></div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

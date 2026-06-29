<?php
/**
 * Mock SMTP Server & Email Catcher in PHP.
 * Listens on port 1025 for local testing.
 * Saves incoming emails to assets/emails/ and opens them in the browser.
 */
set_time_limit(0);
ob_implicit_flush();

$address = '127.0.0.1';
$port = 1025;

$server = @stream_socket_server("tcp://$address:$port", $errno, $errstr);
if (!$server) {
    die("[-] Could not start mail catcher server: $errstr ($errno)\n");
}
echo "[+] SMTP Catcher running on 127.0.0.1:1025...\n";
echo "[+] Waiting for test orders... (Keep this window open while testing)\n\n";

while ($client = @stream_socket_accept($server, -1)) {
    fwrite($client, "220 localhost SMTP Catcher\r\n");
    $data_mode = false;
    $email_content = "";
    
    while ($line = fgets($client, 1024)) {
        if ($data_mode) {
            if (trim($line) === '.') {
                $data_mode = false;
                fwrite($client, "250 OK\r\n");
                
                // Parse email and save/open
                save_and_open_email($email_content);
            } else {
                $email_content .= $line;
            }
        } else {
            $cmd = strtoupper(substr($line, 0, 4));
            if ($cmd === "EHLO" || $cmd === "HELO") {
                fwrite($client, "250-localhost\r\n250 AUTH LOGIN\r\n");
            } elseif ($cmd === "MAIL") {
                fwrite($client, "250 OK\r\n");
            } elseif ($cmd === "RCPT") {
                fwrite($client, "250 OK\r\n");
            } elseif ($cmd === "DATA") {
                $data_mode = true;
                $email_content = "";
                fwrite($client, "354 Start input\r\n");
            } elseif ($cmd === "QUIT") {
                fwrite($client, "221 Goodbye\r\n");
                break;
            } else {
                fwrite($client, "250 OK\r\n");
            }
        }
    }
    fclose($client);
}

function save_and_open_email($content) {
    $dir = __DIR__ . '/assets/emails/';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    $html_file = $dir . 'captured_' . time() . '.html';
    
    // Separate headers from body
    $parts = explode("\r\n\r\n", $content, 2);
    $headers = $parts[0] ?? '';
    $body = $parts[1] ?? $content;
    
    // Parse Subject
    preg_match('/Subject:\s*(.*)/i', $headers, $matches);
    $subject = trim($matches[1] ?? 'Order Confirmation');
    
    // Parse Recipient
    preg_match('/To:\s*(.*)/i', $headers, $matches);
    $to = trim($matches[1] ?? 'Customer');
    
    $html = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Captured Email: $subject</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f7f3eb; padding: 30px; color: #333; margin: 0; }
        .email-card { max-width: 600px; margin: 30px auto; background: white; padding: 35px; border-radius: 16px; box-shadow: 0 8px 24px rgba(139, 69, 19, 0.1); border-top: 10px solid #8B4513; }
        .email-header { text-align: center; margin-bottom: 25px; }
        .email-header h2 { color: #8B4513; margin: 0; font-size: 1.6rem; }
        .meta-info { margin-bottom: 25px; padding: 15px; background-color: #faf8f5; border-radius: 8px; font-size: 0.9rem; color: #666; border-left: 4px solid #D2B48C; }
        .meta-row { margin-bottom: 6px; }
        .meta-label { font-weight: bold; color: #5D2E0A; min-width: 80px; display: inline-block; }
        .content { white-space: pre-wrap; font-size: 1rem; line-height: 1.6; color: #444; background: #fff; border: 1px dashed #ddd; padding: 20px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class='email-card'>
        <div class='email-header'>
            <h2>📧 Mock Email Notification</h2>
        </div>
        <div class='meta-info'>
            <div class='meta-row'><span class='meta-label'>To:</span> " . htmlspecialchars($to) . "</div>
            <div class='meta-row'><span class='meta-label'>Subject:</span> " . htmlspecialchars($subject) . "</div>
            <div class='meta-row'><span class='meta-label'>Time:</span> " . date('Y-m-d H:i:s') . "</div>
        </div>
        <div class='content'>" . htmlspecialchars(trim($body)) . "</div>
    </div>
</body>
</html>";

    file_put_contents($html_file, $html);
    echo "[+] Captured new email to $to! Saved to assets/emails/\n";
    
    // Auto-open in browser!
    $escaped_file = str_replace('/', '\\', $html_file);
    exec("start \"\" \"$escaped_file\"");
}
?>

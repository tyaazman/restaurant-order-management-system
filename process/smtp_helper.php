<?php
/**
 * Sends a real email using standard socket communication with Gmail SMTP.
 * Bypasses local server mail requirements and works on default XAMPP setups.
 */
function send_smtp_email($to, $subject, $message, $smtp_user, $smtp_pass) {
    $host = "ssl://smtp.gmail.com";
    $port = 465;
    
    // Disable SSL peer verification to ensure it runs on local offline servers easily
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);
    
    $socket = @stream_socket_client("$host:$port", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        return false;
    }
    
    // Read welcome response
    fgets($socket, 515);
    
    // Send EHLO
    fwrite($socket, "EHLO localhost\r\n");
    do {
        $response = fgets($socket, 515);
    } while (substr($response, 3, 1) === '-');
    
    // Request AUTH LOGIN
    fwrite($socket, "AUTH LOGIN\r\n");
    fgets($socket, 515);
    
    // Username (Base64)
    fwrite($socket, base64_encode($smtp_user) . "\r\n");
    fgets($socket, 515);
    
    // Password (Base64)
    fwrite($socket, base64_encode($smtp_pass) . "\r\n");
    $response = fgets($socket, 515);
    if (strpos($response, '235') === false) {
        fclose($socket);
        return false; // Authentication failed
    }
    
    // Mail From
    fwrite($socket, "MAIL FROM: <{$smtp_user}>\r\n");
    fgets($socket, 515);
    
    // Recipient
    fwrite($socket, "RCPT TO: <{$to}>\r\n");
    fgets($socket, 515);
    
    // Start data send
    fwrite($socket, "DATA\r\n");
    fgets($socket, 515);
    
    // Send Headers and Message
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "From: Sup Tulang ZZ <{$smtp_user}>\r\n";
    $headers .= "To: {$to}\r\n";
    $headers .= "Subject: {$subject}\r\n";
    
    fwrite($socket, $headers . "\r\n" . $message . "\r\n.\r\n");
    fgets($socket, 515);
    
    // Close
    fwrite($socket, "QUIT\r\n");
    fclose($socket);
    
    return true;
}
?>

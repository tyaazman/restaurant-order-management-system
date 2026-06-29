<?php
return [
    // Set to true to enable sending real emails to customer inboxes via SMTP.
    'enabled' => true,
    
    // For local test catcher, use 127.0.0.1 and port 1025.
    // If you want real emails later, change host to 'ssl://smtp.gmail.com', port to 465, and use your credentials.
    'host' => '127.0.0.1',
    'port' => 1025,
    
    // Your Gmail address or mock sender address
    'email' => 'test@suptulangzz.com',
    
    // Your Google App Password or empty for local testing
    'password' => ''
];
?>

<?php
// Firebase Server Key
define('FIREBASE_SERVER_KEY', 'AIzaSyA441lMxE2ZvMLJD-fugLcf2-DRbCm0QxA'); // Replace with your Firebase server key

// Function to send the notification
function sendFCMNotification($deviceToken, $title, $body, $data) {
    $url = 'https://fcm.googleapis.com/fcm/send';
    
    // Prepare the message payload
    $fields = array(
        'to' => $deviceToken,  // Device token of the recipient
        'notification' => array(
            'title' => $title,
            'body' => $body
        ),
        'data' => $data  // Custom data to send with the notification
    );

    // Prepare headers
    $headers = array(
        'Authorization: key=' . FIREBASE_SERVER_KEY,
        'Content-Type: application/json'
    );

    // cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    
    // Execute the request and close the connection
    $result = curl_exec($ch);
    curl_close($ch);

    // Check for errors
    if ($result) {
        echo "Notification sent successfully!";
    } else {
        echo "Failed to send notification.";
    } 
}
 
// Example usage:
// 1. Send notification to employee (Ride Confirmation)
sendFCMNotification('EMPLOYEE_DEVICE_TOKEN', 'Ride Confirmation', 'Your ride has been confirmed!', array('status' => 'confirmed'));

// 2. Send notification to driver (Ride Assigned)
sendFCMNotification('DRIVER_DEVICE_TOKEN', 'Ride Assigned', 'You have been assigned a ride.', array('status' => 'assigned'));
?>

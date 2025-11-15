<?php

use \SendGrid\Mail\Mail;

function email_send(
    $to_email,
    $to_name,
    $message,
    $subject
)
{

    /*
    echo 'From Name: '.SENDGRID_FROM_NAME.'<br>';
    echo 'From Email: '.SENDGRID_FROM_EMAIL.'<br>';
    echo 'From Name: '.SENDGRID_FROM_NAME.'<br>';
    echo 'From Email: '.SENDGRID_FROM_EMAIL.'<br>';
    */

    /*
    $email = new Mail();
    $email->setFrom(SENDGRID_FROM_EMAIL, SENDGRID_FROM_NAME);
    $email->setSubject($subject);
    $email->addTo($to_email, $to_name);
    $email->addContent("text/html", $message);
    
    $sendgrid = new \SendGrid(SENDGRID_API_KEY);

    try {
        
        $response = $sendgrid->send($email);

        debug_pre($response);

        unset($_SESSION['email']);

        $_SESSION['email'] = array(
            'status_code' => $response->statusCode(),
            'headers' => $response->headers(),
        );

    } catch (Exception $e) {

        echo 'Caught exception: '.  $e->getMessage(). "\n";

    }
    */

    $data = [
        'sender' => [
            'name' => BREVO_FROM_NAME,
            'email' => BREVO_FROM_EMAIL
        ],
        'to' => [
            [
                'email' => $to_email,
                'name' => $to_name
            ]
        ],
        'subject' => $subject,
        'htmlContent' => $message
    ];

    // cURL setup
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'api-key: ' . BREVO_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if (curl_errno($ch)) 
    {
        echo 'Error: ' . curl_error($ch);
    } 
    else 
    {
        echo 'Response: ' . $response;
    }
    
    curl_close($ch);

    print_r($response);
    
}

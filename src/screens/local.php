<?php

function getdatafromTuya(){
    $url = "https://openapi.tuyaeu.com/v2.0/cloud/thing/bf731cd1134d997d8esevj/shadow/properties?codes=temp_current%2Chumidity_value%2Cbattery_percentage";

    // Headers for the request
    $headers = [
        "sign_method: HMAC-SHA256",
        "client_id: us4qr3r7teskf57afq4j",
        "t: 1733917163909",
        "mode: cors",
        "Content-Type: application/json",
        "sign: A272F7CD36B04D2ED2FA78B78C68648E1AA20BB4F91D0ADB6B1A8DCDAE77A1E0",
        "access_token: 5044cef59b7a70fcaf3b048f549a61b6"
    ];
    
    // Create a stream context
    $options = [
        "http" => [
            "method" => "GET",
            "header" => implode("\r\n", $headers)
        ]
    ];
    $context = stream_context_create($options);
    
    // Send the request
    $response = file_get_contents($url, false, $context);
    
    // Check for errors
    if ($response === FALSE) {
        die("Error occurred while making the request.");
    }
    
    // Decode JSON response
    $data = json_decode($response, true);
    
    // Extract and format data
    if (isset($data['result']['properties'])) {
        $properties = $data['result']['properties'];
        foreach ($properties as $property) {
            echo "<p><strong>{$property['code']}</strong>: {$property['value']}</p>";
        }
    } else {
        echo "<p>No data available.</p>";
    }
}
?>

<h1>Local Data</h1>
<div>
    <?php //getdatafromTuya(); ?>
</div>


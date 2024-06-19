<?php

function get_c_token(){
        // URL to send the POST request to
        $url = "https://groupe-mfadel.3cx.ma:5001/webclient/api/Login/GetAccessToken";

        // Parameters to be sent with the POST request
        $data = array(
            'Password' => 'Thethepo06+',
            'Username' => '007',
            'SecurityCode' =>''
        );
         $headers = [
             'Accept: application/json',
             'Accept-Encoding: gzip, deflate, br, zstd',
             'Accept-Language: fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
             'Cache-Control: no-cache',
             'Connection: keep-alive',
             'Content-Type: application/json',
             'Content-Length: 61',
             'Host: groupe-mfadel.3cx.ma:5001',
             'ngsw-bypass: bypass',
             'Priority: u=1',
             'Sec-Fetch-Dest: empty',
             'Sec-Fetch-Mode: cors',
             'Sec-Fetch-Site: same-origin',
             'TE: trailers',
             'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:127.0) Gecko/20100101 Firefox/127.0',
             'Priority: u=1'
         ];

            // Convert the headers array to a string
    $headersString = '';
    foreach ($headers as $header) {
        $headersString .= $header . "\r\n";
    }

    // Convert the data array to a URL-encoded query string
    $postData = http_build_query($data);

    // Set up the stream context options
    $options = array(
        'http' => array(
            'header'  => $headersString,
            'method'  => 'POST',
            'content' => $postData,
        ),
    );

    // Create the stream context
    $context  = stream_context_create($options);

    // Send the POST request and get the response
    $response = file_get_contents($url, false, $context);

    if ($response === FALSE) {
        // Handle error
        die('Error occurred');
    }

    // Decode the JSON response
    $responseData = json_decode($response, true);

    // Output the response
    echo '<pre>';
    print_r($responseData);
    echo '</pre>';
}

function getToken(){
    // URL to send the POST request to
    $url = "https://groupe-mfadel.3cx.ma:5001/webclient/api/Login/GetAccessToken";

    // Parameters to be sent with the POST request
    $data = array(
        'Password' => 'Thethepo06+',
        'Username' => '007',
        'SecurityCode' => ''
    );
     $headers = [
        'Accept: application/json',
        'Accept-Encoding: gzip, deflate, br, zstd',
        'Accept-Language: fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'Content-Type: application/json',
        'Content-Length: 61',
        'Host: groupe-mfadel.3cx.ma:5001',
        'ngsw-bypass: bypass',
        'Priority: u=1',
        'Sec-Fetch-Dest: empty',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Site: same-origin',
        'TE: trailers',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:127.0) Gecko/20100101 Firefox/127.0',
        'Priority: u=1'
     ];
    // Initialize cURL session
    $ch = curl_init($url);

   
// Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_POST, true); // Use POST method
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Send the data
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Set the headers

    // Execute the POST request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
        // Close the cURL session
        curl_close($ch);

        // Decode the JSON response
        $responseData = json_decode($response, true);

        // Output the response
        var_dump( $response );
    }

}
//getToken();
$url = "https://groupe-mfadel.3cx.ma:5001/xapi/v1/ReportCallLogData/Pbx.GetCallLogData(periodFrom=2024-05-31T23%3A00%3A00.000Z,periodTo=2024-06-12T22%3A59%3A59.000Z,sourceType=0,sourceFilter='',destinationType=0,destinationFilter='',callsType=0,callTimeFilterType=0,callTimeFilterFrom='0%3A00%3A0',callTimeFilterTo='0%3A00%3A0',hidePcalls=true)?";
// // Headers
 $headers = [
     'Accept: application/json',
     'Accept-Encoding: gzip, deflate, br, zstd',
     'Accept-Language: fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
     'Authorization: Bearer eyJhbGciOiJIUzI1NiIsImtpZCI6Iko4eXJ3ZVRUQktxQ2RFNTdYaXNnUnciLCJ0eXAiOiJKV1QifQ.eyJ1bmlxdWVfbmFtZSI6IjAwNyIsIkxhc3RVcGRhdGVkIjoiOWMzZjExZWMtOTY5Yy00NTQxLTkzNDgtNDYwYmMzOWNjNjRiIiwicm9sZSI6WyJNeVVzZXIiLCJSZXBvcnRzIiwiUGhvbmVTeXN0ZW1BZG1pbiIsIkdyb3Vwcy5DcmVhdGUiLCJVc2VycyIsIlRydW5rcyIsIk91dGJvdW5kUnVsZXMiLCJHbG9iYWxBZG1pbiIsIkFkbWluIiwiTWFjaGluZUFkbWluIiwiU2luZ2xlQ29tcGFueSIsIlBhaWQiXSwiTWF4Um9sZSI6InN5c3RlbV9vd25lcnMiLCJuYmYiOjE3MTgyODQ5MDksImV4cCI6MTcxODI4ODUwOSwiaWF0IjoxNzE4Mjg0OTA5LCJpc3MiOiJncm91cGUtbWZhZGVsLjNjeC5tYTo1MDAxIiwiYXVkIjoiZ3JvdXBlLW1mYWRlbC4zY3gubWE6NTAwMSJ9.6u4dJc36-E39g39W4V99l7Iu67wlVj2SnqfFVVizMR0',
     'Connection: keep-alive',
     'Host: groupe-mfadel.3cx.ma:5001',
     'ngsw-bypass: bypass',
     'Priority: u=1',
     'Sec-Fetch-Dest: empty',
     'Sec-Fetch-Mode: cors',
     'Sec-Fetch-Site: same-origin',
     'TE: trailers',
     'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:127.0) Gecko/20100101 Firefox/127.0'
 ];

  //Create a stream context
 $options = [
     'http' => [
         'header' => implode("\r\n", $headers)
     ]
 ];

//  $context = stream_context_create($options);

//   //Send the GET request and capture the response
//  $response = file_get_contents($url, false, $context);
// Set cURL options
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
curl_setopt($ch, CURLOPT_POST, true); // Use POST method
//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Send the data
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Set the headers

// Execute the POST request
$response = curl_exec($ch);
// Check for errors
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response
    $responseData = json_decode($response, true);

    // Output the response
    var_dump( $response );
}
//Check if the response is compressed and decompress it
 if ($response !== FALSE) {
     $decodedResponse = false;
     $contentEncoding = $responseData ? implode("\n", $responseData) : '';

     if (strpos($contentEncoding, 'Content-Encoding: gzip') !== false) {
         $decodedResponse = gzdecode($response);
     } elseif (strpos($contentEncoding, 'Content-Encoding: deflate') !== false) {
         $decodedResponse = gzinflate($response);
     } elseif (strpos($contentEncoding, 'Content-Encoding: br') !== false) {
         if (function_exists('brotli_uncompress')) {
             $decodedResponse = brotli_uncompress($response);
         } else {
             echo 'brotli decompression is not available.';
         }
     } elseif (strpos($contentEncoding, 'Content-Encoding: zstd') !== false) {
        //Handle zstd decompression if a library is available
         echo 'zstd decompression is not implemented.';
     } else {
         $decodedResponse = $response;
     }

     if ($decodedResponse !== false) {
         echo $decodedResponse;
     } else {
         echo 'Failed to decompress the response.';
     }
 } else {
     echo 'Error fetching the URL';
 }
?>

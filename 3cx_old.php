<?php
$token="";
$url = "https://groupe-mfadel.3cx.ma:5001/xapi/v1/ReportCallLogData/Pbx.GetCallLogData(periodFrom=2024-02-27T23%3A00%3A00.000Z,periodTo=2024-06-12T22%3A59%3A59.000Z,sourceType=0,sourceFilter='',destinationType=0,destinationFilter='',callsType=0,callTimeFilterType=0,callTimeFilterFrom='0%3A00%3A0',callTimeFilterTo='0%3A00%3A0',hidePcalls=true)?";
$login_url='https://groupe-mfadel.3cx.ma:5001/webclient/api/Login/GetAccessToken';


function get_token_curl($url){
    $token="";
    // Headers
    $headers = [
        'Accept: application/json',
        'Accept-Encoding: gzip, deflate, br, zstd',
        'Accept-Language: fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
        'Content-Type: application/json',
        'Origin: https://groupe-mfadel.3cx.ma:5001',
        'Sec-Fetch-Dest: empty',
        'Sec-Fetch-Mode: no-cors',
        'Sec-Fetch-Site: same-origin',
        'ngsw-bypass: bypass',
        'Pragma: no-cache',
        'Cache-Control: no-cache'
    ];

    // JSON payload
    $data = [
        'SecurityCode' => '',
        'Password' => 'Thethepo06+',
        'Username' => '007'
    ];

    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_ENCODING, ''); // Automatically handle the response encoding
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HEADER, true); // Include headers in the output
    curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output

    // Send the request and capture the response
    $response = curl_exec($ch);

    // Check for cURL errors
    if ($response === false) {
        echo 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        exit;
    }

    // Get the HTTP response code
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Separate headers and body
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    // Close the cURL session
    curl_close($ch);


    // Parse JSON to extract and display the response data
    $data = json_decode($body, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $token=$data["Token"]["token_type"]." ".$data["Token"]["access_token"];
    } else {
        echo "\nFailed to parse JSON response. Error: " . json_last_error_msg() . "\n";
    }
    return $token;
}
function get_data_curl($url,$token){
        
    // Initialize cURL session
    $ch = curl_init();

    // Set the URL
    curl_setopt($ch, CURLOPT_URL, $url);

    // Set the HTTP method to GET
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    // Set the headers
    $headers = [
        'Accept: application/json',
        'Accept-Encoding: gzip, deflate, br, zstd',
        'Accept-Language: fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
        'Authorization: '.$token,
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
    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_ENCODING, ''); // Automatically handle the response encoding
    curl_setopt($ch, CURLOPT_HEADER, true); // Include headers in the output
    curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output

    // Send the request and capture the response
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if ($response === false) {
        echo 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        
    }

    // Get the HTTP response code
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   
    if($http_code=='401'){
        echo "mauvais token";
        exit();
    }
    
    // Separate headers and body
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    $data = json_decode($body, true);
    // Close the cURL session
    insert_data($data["value"]);
    curl_close($ch);
}

function insert_data($data){
    $servername = "172.28.0.22";
    $username = "sa";
    $password = "MG+P@ssw0rd";
    $dbname = "3CX";
    // Create connection

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // SQL to delete the table if it exists
    $sql = "DROP TABLE IF EXISTS call_logs";
    if ($conn->query($sql) === TRUE) {
        echo "Table call_logs deleted successfully\n";
    } else {
        echo "Error deleting table: " . $conn->error . "\n";
    }

    $sql = "
    CREATE TABLE call_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        CallId INT NOT NULL,
        Indent INT,
        StartTime DATETIME,
        SourceType INT,
        SourceDn VARCHAR(200),
        SourceCallerId VARCHAR(200),
        SourceDisplayName VARCHAR(200),
        DestinationType INT,
        DestinationDn VARCHAR(200),
        DestinationCallerId VARCHAR(200),
        DestinationDisplayName VARCHAR(200),
        ActionType INT,
        ActionDnType INT,
        ActionDnDn VARCHAR(200),
        ActionDnCallerId VARCHAR(200),
        ActionDnDisplayName VARCHAR(200),
        RingingDuration TIME,
        TalkingDuration TIME,
        CallCost INT,
        Answered BOOLEAN,
        RecordingUrl VARCHAR(255),
        SubrowDescNumber INT,
        Reason VARCHAR(255),
        SegmentId INT,
        QualityReport BOOLEAN
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table call_logs created successfully\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }
        // Convert RingingDuration and TalkingDuration to MySQL TIME format for each row
    
    foreach ($data as $row) {
        $row["TalkingDuration"] = $row["TalkingDuration"] ?? '0';
        $row["RingingDuration"] = gmdate("H:i:s", $seconds = (float) filter_var($row["RingingDuration"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
        $row["TalkingDuration"] = gmdate("H:i:s", $seconds = (float) filter_var($row["TalkingDuration"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
        $row["StartTime"] = date('Y-m-d H:i:s', strtotime($row["StartTime"]));
        // Insert data into the table
        $stmt = $conn->prepare("
            INSERT INTO call_logs (
                CallId, Indent, StartTime, SourceType, SourceDn, SourceCallerId,
                SourceDisplayName, DestinationType, DestinationDn, DestinationCallerId,
                DestinationDisplayName, ActionType, ActionDnType, ActionDnDn, ActionDnCallerId,
                ActionDnDisplayName, RingingDuration, TalkingDuration, CallCost, Answered,
                RecordingUrl, SubrowDescNumber, Reason, SegmentId, QualityReport
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisissssisssissssssisiisi",
            $row["CallId"], $row["Indent"], $row["StartTime"], $row["SourceType"], $row["SourceDn"],
            $row["SourceCallerId"], $row["SourceDisplayName"], $row["DestinationType"], $row["DestinationDn"],
            $row["DestinationCallerId"], $row["DestinationDisplayName"], $row["ActionType"], $row["ActionDnType"],
            $row["ActionDnDn"], $row["ActionDnCallerId"], $row["ActionDnDisplayName"], $row["RingingDuration"],
            $row["TalkingDuration"], $row["CallCost"], $row["Answered"], $row["RecordingUrl"],
            $row["SubrowDescNumber"], $row["Reason"], $row["SegmentId"], $row["QualityReport"]
        );

        if ($stmt->execute()) {
           // echo "New record created successfully\n";
        } else {
            echo "Error: " . $stmt->error . "\n";
        }

        // Close statement for next iteration
        $stmt->close();
    }
    echo "All inserted successfully\n";

// Close connections
$conn->close();
}
$token=get_token_curl($login_url);
// var_dump($token);
get_data_curl($url,$token);
?>

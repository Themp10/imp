<?php
session_start();
$servername = "172.28.0.22";
$username = "sa";
$password = "MG+P@ssw0rd";
$dbname = "INV";
// Create connection

$Invconn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($Invconn->connect_error) {
    die("Connection failed: " . $Invconn->connect_error);
}

$payload = json_decode(file_get_contents('php://input'), true);

function get_compteur($type){
    global $Invconn; 
    $sql="SELECT compteur FROM compteurs where type='".$type."'"; 
    $result = $Invconn->query($sql);
    if ($result === false) {
        die("Error in SQL query: " . $Invconn->error);
    }
    $compteur = [];

    while ($row = $result->fetch_assoc()) {
        $compteur[] = $row;
    }
    return $compteur[0]["compteur"];
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($payload && isset($payload['key']) && isset($payload['data'])) {
        $code="";
        $status="err";
        $item=$payload['data'][0];
        if($payload['key']=='new'){
            $type=$item['type'];
            $compteur=get_compteur($type);
            $code = $type . str_pad(strval($compteur), 6, "0", STR_PAD_LEFT);
            $designation=$item['designation'];
            $numero_serie=$item['numero_serie'];
            $marque=$item['marque'];
            $modele=$item['modele'];
            $date_acquisition=$item['date_acquisition'];
            $statut=$item['statut'];
            $etat=$item['etat'];
            $utilisateur=$item['utilisateur'];
            $emplacement=$item['emplacement'];
            $valeur=$item['valeur'];
            $commentaire=$item['commentaire'];
            $insertSql = "
            INSERT INTO items (
                code, type, designation, numero_serie, marque, modele, 
                date_acquisition, statut, etat, utilisateur, emplacement, 
                valeur, commentaire
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            if ($stmt = $Invconn->prepare($insertSql)) {
                $stmt->bind_param(
                    'sssssssssssds',
                    $code, $type, $designation, $numero_serie, $marque, $modele,
                    $date_acquisition, $statut, $etat, $utilisateur, $emplacement,
                    $valeur, $commentaire
                );
                if (!$stmt->execute()) {
                    echo json_encode(['status' => 'error', 'message' => 'Insert execution failed: ' . $stmt->error]);
                    exit;
                }else{
                    $status="success";
                    $query = "UPDATE compteurs SET compteur = ? WHERE type = ?";
                    if ($stmt = $Invconn->prepare($query)) {
                        $newCompteur=$compteur+1;
                        $stmt->bind_param('is', $newCompteur, $type);
                        if (!$stmt->execute()) {
                            echo json_encode(['status' => 'error', 'message' => 'Validation Query execution failed: ' . $stmt->error]);
                            exit;
                        }
                    }
                }
                $stmt->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $Invconn->error]);
            }
        }elseif($payload['key']=='update'){

        }
    }

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array(
            "status" => $status,
            "code" => $code,
        ));
    exit();
} else {    
echo json_encode(['status' => 'error', 'message' => 'Invalid input or missing data']);
}   
$Invconn->close();
?>
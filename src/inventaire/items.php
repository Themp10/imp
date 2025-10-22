<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$admin='admin@groupemfadel.com';
$adminPass='wnga gixg tdbg wnrx';
require __DIR__ . '/../../vendor/autoload.php';

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

function send_mail($data){
    global $admin;
    global $adminPass;
    $mail = new PHPMailer(true);
}
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
function getReaffectationChanges($code) {
    global $Invconn; 
    $stmt = $Invconn->prepare("SELECT * FROM reaffectation WHERE code = ? ORDER BY date");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    $html = "";

    while ($row = $result->fetch_assoc()) {
        $changes = [];
        foreach ($row as $column => $value) {
            if (strpos($column, 'new_') === 0) {
                $field = substr($column, 4);
                $oldCol = 'old_' . $field;

                if (isset($row[$oldCol]) && $row[$oldCol] != $value) {
                    $changes[$field] = [
                        'old' => $row[$oldCol],
                        'new' => $value
                    ];
                }
            }
        }

        if (!empty($changes)) {
            $html .= "<div class='reaffectation-block'>";
            $html .= "<h3>" . htmlspecialchars($row['date']) . "</h3>";
            $html .= "<ul>";
            foreach ($changes as $field => $vals) {
                $html .= "<li><strong>" . htmlspecialchars($field) . "</strong>: "
                    . htmlspecialchars($vals['old']) . " → "
                    . "<span style='color:green;'>" . htmlspecialchars($vals['new']) . "</span></li>";
            }
            $html .= "</ul>";
            $html .= "</div>";
        }
    }

    return $html ?: "<p>Aucune réaffectation trouvée.</p>";
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status="";
    $html="";
    $code="";
    if ($payload && isset($payload['key']) && isset($payload['data'])) {
        
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
            $date_affectation=$item['date_affectation'];
            $statut=$item['statut'];
            $etat=$item['etat'];
            $utilisateur=$item['utilisateur'];
            $direction=$item['direction'];
            $poste=$item['poste'];
            $emplacement=$item['emplacement'];
            $site=$item['site'];
            $valeur=$item['valeur'];
            $commentaire=$item['commentaire'];
            $insertSql = "
            INSERT INTO items (
                code, type, designation, numero_serie, marque, modele, 
                date_acquisition,date_affectation, statut, etat, utilisateur, emplacement, 
                valeur, commentaire, direction, poste, site
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            if ($stmt = $Invconn->prepare($insertSql)) {
                $stmt->bind_param(
                    'ssssssssssssdssss',
                    $code, $type, $designation, $numero_serie, $marque, $modele,
                    $date_acquisition,$date_affectation, $statut, $etat, $utilisateur, $emplacement,
                    $valeur, $commentaire, $direction, $poste, $site
                );
                if (!$stmt->execute()) {
                    echo json_encode(['status' => 'error', 'message' => 'Insert execution failed: ' . $stmt->error]);
                    exit;
                }else{
                    $query = "UPDATE compteurs SET compteur = ? WHERE type = ?";
                    if ($stmt = $Invconn->prepare($query)) {
                        $newCompteur=$compteur+1;
                        $stmt->bind_param('is', $newCompteur, $type);
                        if (!$stmt->execute()) {
                            echo json_encode(['status' => 'error', 'message' => 'Validation Query execution failed: ' . $stmt->error]);
                            exit;
                        }else{
                            $status="Insert Success";
                        }
                    }
                }
                $stmt->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $Invconn->error]);
            }
        }elseif($payload['key']=='search' || $payload['key']=='search2'){
            $func="";
            if($payload['key']=='search'){
                $func="selectSearchItem(this)";
            }elseif($payload['key']=='search2'){
                $func="selectSearchItem2(this)";
            }
            $code="";
            $item = $payload['data'];
            $search = '%' . $item . '%';
            
            $sql = "SELECT code, CONCAT(code,' - ', designation,' - ', numero_serie,' - ', marque,' - ', modele,' - ', emplacement,' - ', utilisateur,' - ', etat) as item FROM items WHERE CONCAT(code,' ', designation,' ', numero_serie,' ', marque,' ', modele,' ', emplacement,' ', utilisateur,' ', etat) LIKE ? LIMIT 10";
            if ($stmt = $Invconn->prepare($sql)) {
                $stmt->bind_param('s', $search);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
    
                    while ($row = $result->fetch_assoc()) {
                        $html.= '<div class="inv-item" onclick="'.$func.'" item-code="' . htmlspecialchars($row['code']) . '">' . htmlspecialchars($row['item']) . '</div>';
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Validation Query execution failed: ' . $stmt->error]);
                }
    
                $stmt->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $Invconn->error]);
            }

        }elseif ($payload['key'] == 'update') {
            $code = $item['code'];
            $type=$item['type'];
            $designation=$item['designation'];
            $numero_serie=$item['numero_serie'];
            $marque=$item['marque'];
            $modele=$item['modele'];
            $date_affectation=$item['date_affectation'];
            $statut=$item['statut'];
            $etat=$item['etat'];
            $utilisateur=$item['utilisateur'];
            $direction=$item['direction'];
            $poste=$item['poste'];
            $site=$item['site'];
            $emplacement=$item['emplacement'];
            $valeur=$item['valeur'];
            $commentaire=$item['commentaire'];
            $updateSql = "
            UPDATE items SET type = ?, designation = ?, numero_serie = ?, marque = ?, modele = ?, date_affectation = ?, statut = ?, etat = ?, utilisateur = ?, emplacement = ?, valeur = ?, commentaire = ?, direction = ?, poste = ?, site = ?
            WHERE code = ?
            ";
            if ($stmt = $Invconn->prepare($updateSql)) {
                $stmt->bind_param(
                    'ssssssssssdsssss',
                    $type, $designation, $numero_serie, $marque, $modele,
                    $date_affectation, $statut, $etat, $utilisateur, $emplacement,
                    $valeur, $commentaire, $direction, $poste, $site, $code
                );
                if (!$stmt->execute()) {
                    echo json_encode(['status' => 'error', 'message' => 'Update execution failed: ' . $stmterror]);
                    exit;
                } else {
                    $status="Update Success";
                }
                $stmt->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $Invconn->error]);
            }
        }elseif ($payload['key'] == 'reaffectation') {
            $code = $item['code'];
            $date_reaffectation=$item['date_reaffectation'];
            $Nstatut=$item['Nstatut'];
            $Netat=$item['Netat'];
            $Nutilisateur=$item['Nutilisateur'];
            $Ndirection=$item['Ndirection'];
            $Nposte=$item['Nposte'];
            $Nsite=$item['Nsite'];
            $Nemplacement=$item['Nemplacement'];
            $Ostatut=$item['Ostatut'];
            $Oetat=$item['Oetat'];
            $Outilisateur=$item['Outilisateur'];
            $Odirection=$item['Odirection'];
            $Oposte=$item['Oposte'];
            $Osite=$item['Osite'];
            $Oemplacement=$item['Oemplacement'];
            $Rcommentaire=$item['Rcommentaire'];
            $date_insert=$datesortie = date("Y-m-d");
            $insertSql = "
            INSERT INTO reaffectation (
                date, code, 
                old_statut, old_etat, old_utilisateur, old_poste, old_direction, old_emplacement, old_site,
                new_statut, new_etat, new_utilisateur, new_poste, new_direction, new_emplacement, new_site,
                commentaire, date_insert
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = $Invconn->prepare($insertSql)) {
                $stmt->bind_param(
                    'ssssssssssssssssss',
                    $date_reaffectation, $code, 
                    $Ostatut, $Oetat, $Outilisateur, $Oposte, $Odirection, $Oemplacement, $Osite,
                    $Nstatut, $Netat, $Nutilisateur, $Nposte, $Ndirection, $Nemplacement, $Nsite,
                    $Rcommentaire, $date_insert
                );
                if (!$stmt->execute()) {
                    echo json_encode(['status' => 'error', 'message' => 'Insert execution failed: ' . $stmt->error]);
                    exit;
                }else{
                        $query = "
                UPDATE items SET date_affectation = ?, statut = ?, etat = ?, utilisateur = ?, emplacement = ?, commentaire = ?, direction = ?, poste = ?, site = ?
                WHERE code = ?
                ";
                        if ($stmt = $Invconn->prepare($query)) {
                            $stmt->bind_param('ssssssssss', $date_reaffectation,$Nstatut, $Netat, $Nutilisateur, $Nemplacement, $Rcommentaire, $Ndirection, $Nposte, $Nsite, $code);
                            if (!$stmt->execute()) {
                                echo json_encode(['status' => 'error', 'message' => 'Validation Query execution failed: ' . $stmt->error]);
                                exit;
                            }else{
                                $status="Insert Success";
                            }
                        }
                    }
                    $stmt->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $Invconn->error]);
            }

        }elseif ($payload['key'] == 'getHistory') {
            $item = $payload['data'];
            $status="Get Success";
            $code=$item;
            $html=getReaffectationChanges($item);
        }elseif ($payload['key'] == 'dechargeList') {
            $items = $payload['data'];
            $code="";
            foreach ($items as $item ) {
                $insertSql = "INSERT INTO decharges (code) VALUES (?)";
                if ($stmt = $Invconn->prepare($insertSql)) {
                        $stmt->bind_param('s', $item);
                        if (!$stmt->execute()) {
                            echo json_encode(['status' => 'error', 'message' => 'Validation Query execution failed: ' . $stmt->error]);
                            exit;
                        }else{
                            $status="success";
                            
                        }
                    }
                    $code.=$item."|";
                }
                
                $stmt->close();
        }elseif ($payload['key'] == 'getDecharge') {
            $htmlToCreate="";
            $htmlToComplete="";
            $htmlToPrint="";
            $code="";
            $sql = "SELECT * from decharges";
            if ($stmt = $Invconn->prepare($sql)) {
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
    
                    while ($row = $result->fetch_assoc()) {
                        if($row['it_statut']==0 && $row['rh_statut']==0){
                            $htmlToCreate.='<div class="decharge-item-card" onclick="getDechargeDetails(' . htmlspecialchars($row['id']) . ',\'IT\')"><p>' . htmlspecialchars($row['code']) . '</p></div>';
                        }elseif ($row['it_statut']==1 && $row['rh_statut']==0) {
                            $htmlToComplete.='<div class="decharge-item-card" onclick="getDechargeDetails(' . htmlspecialchars($row['id']) . ',\'RH\')"><p>' . htmlspecialchars($row['code']) . '</p></div>';
                        }elseif ($row['it_statut']==1 && $row['rh_statut']==1) {
                            $htmlToPrint.='<div class="decharge-item-card" onclick="getDechargeDetails(' . htmlspecialchars($row['id']) . ',\'IMP\')"><p>' . htmlspecialchars($row['code']) . '</p></div>';
                        }
                    }
                } 
                if ($htmlToCreate=="") {
                    $htmlToCreate='<p class="text-italic">Aucune décharge à traiter</p>';
                }
                if ($htmlToComplete=="") {
                    $htmlToComplete='<p class="text-italic">Aucune décharge à traiter</p>';
                }
                if ($htmlToPrint=="") {
                    $htmlToPrint='<p class="text-italic">Aucune décharge à traiter</p>';
                }
                $html=array(
                            "htmlToCreate" => $htmlToCreate,
                            "htmlToComplete" => $htmlToComplete,
                            "htmlToPrint" => $htmlToPrint,
                );
                
                $stmt->close();
        }
            
    }elseif ($payload['key'] == 'getDechargeDetail') {
            $sql='SELECT d.id,i.code,CONCAT(i.designation," ",i.marque," ",i.modele) AS "materiel",i.numero_serie ,i.etat,d.description,coalesce(d.user,i.utilisateur) as "user",d.societe,d.poste_user,d.adresse_user,d.date_decharge  
                FROM decharges d
                LEFT JOIN items i ON d.code=i.code
                WHERE d.id=?';
            $stmt = $Invconn->prepare($sql);
            $stmt->bind_param("s", $item);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                echo "Aucune decharge trouvée avec le code: $item";
                exit;
            }
            
            $html = $result->fetch_assoc();
    }elseif ($payload['key'] == 'updateDechargeIT') {
            $id = $item['id'];
            $materiel=$item['materiel'];
            $description=$item['description'];
            $user=$item['user'];
            $query = "UPDATE decharges SET materiel = ?, description = ?, user = ?, it_statut = 1 WHERE id = ?";
            if ($stmt = $Invconn->prepare($query)) {
                $stmt->bind_param('sssi', $materiel,$description, $user, $id);
                if (!$stmt->execute()) {
                    echo json_encode(['status' => 'error', 'message' => 'Validation Query execution failed: ' . $stmt->error]);
                    exit;
                }else{
                    $status="update success";
                }
            }
            $stmt->close();
    }elseif ($payload['key'] == 'updateDechargeRH') {
            $id = $item['id'];
            $poste_user=$item['poste_user'];
            $adresse_user=$item['adresse_user'];
            $societe=$item['societe'];
            $user=$item['user'];
            $date = date('Y/m/d H:i:s');
            $query = "UPDATE decharges SET poste_user = ?, adresse_user = ?, societe = ?, user = ?, rh_statut = 1 WHERE id = ?";
            if ($stmt = $Invconn->prepare($query)) {
                $stmt->bind_param('ssisi', $poste_user,$adresse_user,$societe, $user, $id);
                if (!$stmt->execute()) {
                    echo json_encode(['status' => 'error', 'message' => 'Validation Query execution failed: ' . $stmt->error]);
                    exit;
                }else{
                    $status="update success";
                }
            }
            $stmt->close();
    }elseif ($payload['key'] == 'printDecharge') {
            $id = $item['id'];
            $code=$item['code'];        
            $date = date('Y/m/d');
            $query = "UPDATE decharges SET date_decharge = ?, printed = 1 WHERE id = ?";
            if ($stmt = $Invconn->prepare($query)) {
                $stmt->bind_param('si', $date, $id);
                if (!$stmt->execute()) {
                    echo json_encode(['status' => 'error', 'message' => 'Validation Query execution failed: ' . $stmt->error]);
                    exit;
                }else{
                    $status="update success";
                    $html=date('d/m/Y');
                }
            }
            $stmt->close();
    }

    }

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array(
            "status" => $status,
            "code" => $code,
            "html" => $html,
        ));
    exit();

} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {    
    if (!isset($_GET['key']) || !isset($_GET['itemCode'])) {
        echo json_encode(['error' => 'Missing parameters']);
        http_response_code(400);
        exit;
    }
    $key = $_GET['key'];
    $itemCode = $_GET['itemCode'];
    if ($key === 'getItem') {
        $stmt = $Invconn->prepare("SELECT * FROM items WHERE code = ?");
        $stmt->bind_param("s", $itemCode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo "Aucun item trouvé avec le code: $itemCode";
            exit;
        }
        
        $item = $result->fetch_assoc();
    }
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array(
            "item" => $item
        ));
    exit();
}else{
echo json_encode(['status' => 'error', 'message' => 'Invalid input or missing data']);
}   
$Invconn->close();
?>
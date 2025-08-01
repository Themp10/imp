<?php
$servername = "localhost";
$username = "sa";
$password = "MG+P@ssw0rd"; // change if needed
$dbname = "INV"; // change to your DB name

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
function convertDate($dateStr) {
    // Handle empty or invalid date
    if (empty($dateStr)) return null;

    // Expecting dd/mm/yyyy
    $parts = explode('/', $dateStr);
    if (count($parts) !== 3) return null;

    // Rearrange to yyyy-mm-dd
    return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
}
// Open the file (exported from Excel as tab-delimited .txt)
$file = 'data.txt'; // path to your file
$handle = fopen($file, "r");
if ($handle === false) {
    die("Cannot open file: " . $file);
}

// Skip the header line
$header = fgets($handle);

// Prepare insert query
$stmt = $conn->prepare("
    INSERT INTO items (
        type, code, designation, numero_serie, marque, modele,
        date_acquisition, date_affectation, statut, etat, utilisateur, Poste,
        Direction, Site, emplacement, valeur, commentaire, departement, actif
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssssssssssssssssi",
    $type, $code, $designation, $numero_serie, $marque, $modele,
    $date_acquisition, $date_affectation, $statut, $etat, $utilisateur, $Poste,
    $Direction, $Site, $emplacement, $valeur, $commentaire, $departement, $actif
);

while (($line = fgets($handle)) !== false) {
    $fields = explode("\t", trim($line)); // tab-delimited

    // Map fields (order must match your header)
    $type = $fields[0] ?? '';
    $code = $fields[1] ?? '';
    $designation = $fields[2] ?? '';
    $numero_serie = $fields[3] ?? '';
    $marque = $fields[4] ?? '';
    $modele = $fields[5] ?? '';
    $date_acquisition = convertDate($fields[6] ?? '');
    $date_affectation = !empty($fields[7]) ? convertDate($fields[7]) : null;
    $statut = $fields[8] ?? '';
    $etat = $fields[9] ?? '';
    $utilisateur = $fields[10] ?? '';
    $Poste = $fields[11] ?? '';
    $Direction = $fields[12] ?? '';
    $Site = $fields[13] ?? '';
    $emplacement = $fields[14] ?? '';
    $valeur = is_numeric($fields[15]) ? $fields[15] : 0.00;
    $commentaire = $fields[16] ?? '';
    $departement = $fields[17] ?? '';
    $actif = is_numeric($fields[18]) ? (int)$fields[18] : 1;

    $stmt->execute();
}

fclose($handle);
$stmt->close();
$conn->close();

echo "Data imported successfully!";
?>

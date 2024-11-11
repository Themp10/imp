<?php
// include_once "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";


function insert_tel($name,$ext){
    
        //quand je dis wrong, c'est que ce n'est absolument les bonnes pratiques et je n'ai pas eu le temps de regler ca
    //!this is very wrong but it was the only way to make it work
    $servername = "172.28.0.22";
    $username = "sa";
    $password = "MG+P@ssw0rd";
    $dbname = "PRINTERS";
    // Create connection

    $conn = new mysqli($servername, $username, $password, $dbname); 
    $insertQuery = "INSERT INTO telephones (Nom,Ext) VALUES ('$name','$ext')";  
    if ($conn->query($insertQuery) === TRUE) {
        $id = $conn->insert_id;
        var_dump($id);
    } else {
        return 'Error creating user : ' . $conn->error;
    }
}
function get_tels(){
    global $conn;
    $sql = "SELECT * FROM telephones";
    $result = $conn->query($sql);
    $html='';
    if ($result === false) {
        die("Error in SQL query: " . $conn->error);
    }


    while ($row = $result->fetch_assoc()) {
        $html.=render_card($row['Nom'],$row['Ext']);
    }

    return $html;
}

function render_card($name,$ext){
    $html='<a class="tel-data" href="tel:'.$ext.'">';
    $html.='<img src="./assets/man.png" alt="Logo" class="tel-img">';
    $html.='<div class="tel-name">'.$name.'</div>';
    $html.='<div class="tel-ext">'.$ext.'</div>';
    $html.='</a>';
    return $html;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    var_dump($_POST);
    if(isset($_POST['tel-name'], $_POST['tel-ext']) && !empty($_POST['tel-name'])&& !empty($_POST['tel-ext'])){
        $name = $_POST['tel-name'];
        $ext = $_POST['tel-ext'];
        insert_tel($name, $ext);
        //exit(); 
    } else {
        // Handle the case where one or both fields are empty
        echo "Both LDAP Login and SAP Login must be filled out.";
        //exit();
    }
    header('Location: /imp/main.php');
}
?>

<h2>Ajouter nouvelle Extention</h2>
<div class="header-switch">
    <form action="src/screens/tel.php" method="post">
        <input type="text" id="tel-name"  name="tel-name" class="tel-text" placeholder="Prénom Nom">
        <input type="text" id="tel-ext"  name="tel-ext" class="tel-text" placeholder="Extention">
        <button class="btn-switch"  type="submit">Ajouter</button>

    </form>

</div>

<h2>Appeler</h2>
<div class="tel-container">
<?php echo get_tels(); ?>
</div>
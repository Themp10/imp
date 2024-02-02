<?php
//include_once "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";


function get_users(){
    global $conn; 
    $sql="SELECT ldap_user,sap_user,profile FROM users"; 
    $result = $conn->query($sql);
    if ($result === false) {
        die("Error in SQL query: " . $conn->error);
    }
    $users_list = [];

    while ($row = $result->fetch_assoc()) {
        $users_list[] = $row;
    }
    return $users_list;
}

function insert_user($ldap_login,$sap_login,$profile){
    //quand je dis wrong, c'est que ce n'est absolument les bonnes pratiques et je n'ai pas eu le temps de regler ca
    //!this is very wrong but it was the only way to make it work
    $servername = "172.28.0.22";
    $username = "sa";
    $password = "MG+P@ssw0rd";
    $dbname = "PRINTERS";
    // Create connection

    $conn = new mysqli($servername, $username, $password, $dbname); 
    $insertQuery = "INSERT INTO users (ldap_user,sap_user,profile) VALUES ('$ldap_login','$sap_login','$profile')";  
    if ($conn->query($insertQuery) === TRUE) {
        $id = $conn->insert_id;
        
    } else {
        return 'Error creating user : ' . $conn->error;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['ldap-login'], $_POST['sap-login'], $_POST['profile']) && !empty($_POST['profile'])&& !empty($_POST['ldap-login']) && !empty($_POST['sap-login'])){
        $ldap_login = $_POST['ldap-login'];
        $sap_login = $_POST['sap-login'];
        $profile = $_POST['profile'];
        insert_user($ldap_login, $sap_login,$profile);
        //exit(); 
    } else {
        // Handle the case where one or both fields are empty
        echo "Both LDAP Login and SAP Login must be filled out.";
        //exit();
    }
    header('Location: /imp/main.php');
}
?>


<div class="users-header">
    <h2>Correspondance SAP - LDAP </h2>
</div>

<div class="users-main-container">
    <div class="users-left">
        <form action="src/screens/users.php" method="post" class="users-form">
            <input type="text" id="ldap-login" name="ldap-login" placeholder="LDAP Login" class="users-input">
            <input type="text" id="sap-login" name="sap-login" placeholder="SAP Login" class="users-input">
            <fieldset class="profile-box">
                <legend class="filter-type-title">  Profile  </legend>
                <select id="user-profile" class="select-filter" name="profile" onchange="handleSelectChange()">
                    <option value="user">Utilisateur</option>
                    <option value="achat">Achat</option>
                    <option value="admin">Admin</option>
                </select>
            </fieldset>
            <button type="submit"  class="users-btn">Enregistrer</button>
        </form>
    </div>
    <div class="users-right">
        <table class="users-table">
            <thead>
                <tr>
                <th>Login LDAP</th>
                <th>Login SAP</th>
                <th>Profile</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach(get_users() as $item){ ?>
                <tr>
                    <td><?php echo $item["ldap_user"]; ?></td>
                    <td><?php echo $item["sap_user"]; ?></td>
                    <td><?php echo $item["profile"]; ?></td>
                </tr>
                <?php } ?>


            </tbody>
        </table>
    </div>
</div>
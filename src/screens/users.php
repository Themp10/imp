<?php
//include_once "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";


function get_users(){
    global $conn; 
    $sql="SELECT ldap_user,sap_user FROM users"; 
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

function insert_user($ldap_login,$sap_login){
    include_once "../db/db_connection.php"; 
    global $conn;  
    $insertQuery = "INSERT INTO users (ldap_user,sap_user) VALUES ('$ldap_login','$sap_login')";
    var_dump($insertQuery);
    if ($conn->query($insertQuery) === TRUE) {
        $id = $conn->insert_id;
        
    } else {
        return 'Error creating user : ' . $conn->error;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['ldap-login'], $_POST['sap-login']) && !empty($_POST['ldap-login']) && !empty($_POST['sap-login'])){
        $ldap_login = $_POST['ldap-login'];
        $sap_login = $_POST['sap-login'];
        insert_user($ldap_login, $sap_login);
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
            <button type="submit"  class="users-btn">Enregistrer</button>
        </form>
    </div>
    <div class="users-right">
        <table class="users-table">
            <thead>
                <tr>
                <th>Login LDAP</th>
                <th>Login SAP</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach(get_users() as $item){ ?>
                <tr>
                    <td><?php echo $item["ldap_user"]; ?></td>
                    <td><?php echo $item["sap_user"]; ?></td>
                </tr>
                <?php } ?>


            </tbody>
        </table>
    </div>
</div>
<?php
session_start();
define('BASEDIR', __DIR__);
require "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";

function get_user($user){
  global $conn; 
  $sql="SELECT sap_user,profile FROM users where ldap_user='".$user."'"; 
  echo $sql;
  $result = $conn->query($sql);
  if ($result === false) {
      die("Error in SQL query: " . $conn->error);
  }
  $user_data = [];

  while ($row = $result->fetch_assoc()) {
      $user_data[] = $row;
  }
  return $user_data[0];
}




if (isset($_SESSION['error_message'])) {
    $cnn_err = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear the message after use
}else{
    $cnn_err='';
}


function init_login($username,$password){
    global $cnn_err;
    $host = 'SERV020';
    $domain = '172.28.0.20';
    $basedn = 'ou=Groupe Mfadel,dc=csi,dc=local';
    $ad = ldap_connect("{$domain}") or die('Could not connect to LDAP server.');
    ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);
    //ldap_bind($ad, "{$user}@csi.local", $password) or die('Could not bind to AD.');
      if(ldap_bind($ad, "{$username}@csi.local", $password)) {
            // User is authenticated
            //session_start();
            // $arr=str_split($username, 1);
            // $init=$arr[0];
            // array_shift($arr);
            $user_data=get_user($username);
            $_SESSION['user'] =$user_data['sap_user']; 
            $_SESSION['profile'] =$user_data['profile'] ;
            $_SESSION['connected'] = true;
            header('Location: main.php');
        } else {
            $_SESSION['connected'] = false;
            $cnn_err= 'Login ou mot de passe incorrect';
            $_SESSION['error_message'] = $cnn_err;
            header('Location: index.php');
            exit;
            // User authentication failed
        }
    ldap_unbind($ad);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $user = $_POST['username'];
    $password = $_POST['password'];
    init_login($user,$password);
    exit(); 
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestionnaire de Stock de toner</title>
<link rel="stylesheet" href="assets/style.css">
<link rel="icon" href="assets/icon.png">
<style>

</style>
</head>
<div class="login-body">
    <img src="assets/MG-logo.png" alt="Girl in a jacket" width="500" height="200"> 
  <div class="login-container">
    <h2>Connexion à votre compte</h2>
    <form action="index.php" method="post">
      <input class="login-input" type="text" name="username" placeholder="Identifiant" required>
      <input class="login-input" type="password" name="password" placeholder="Mot de passe" required>

      <button type="submit" class="login-submit">Se connecter</button>
    </form>
    <div class="login-err"><?php echo $cnn_err;?></div>
    <div class="footer">
      GROUPE MFADEL - DOSI
    </div>
  </div>
</div>
</html>

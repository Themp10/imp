<?php
include "db_connection.php"; // Include database connection file

// Fetch and display the list of cartridges
$sql = "SELECT * FROM cartridges";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="assets/main.css">
</head>
<body>
    
<header>
  <h1>Drag & Drop<br/><span>Lean Kanban Board</span></h1>
</header>

<div class="add-task-container">
  <input type="text" maxlength="12" id="taskText" placeholder="New Task..." onkeydown="if (event.keyCode == 13)
                        document.getElementById('add').click()">
  <button id="add" class="button add-button" onclick="addTask()">Add New Task</button>
</div>




</body>
</html>
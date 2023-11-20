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

<div class="main-container">
  <ul class="columns">

    <li class="column to-do-column">
      <div class="column-header">
        <h4>To Do</h4>
      </div>
      <ul class="task-list" id="to-do">
        <li class="task">
          <p>Analysis</p>
        </li>
        <li class="task">
          <p>Coding</p>
        </li>
        <li class="task">
          <p>Card Sorting</p>
        </li>
        <li class="task">
          <p>Measure</p>
        </li>
      </ul>
    </li>

    <li class="column doing-column">
      <div class="column-header">
        <h4>Doing</h4>
      </div>
      <ul class="task-list" id="doing">
        <li class="task">
          <p>Hypothesis</p>
        </li>
        <li class="task">
          <p>User Testing</p>
        </li>
        <li class="task">
          <p>Prototype</p>
        </li>
      </ul>
    </li>

    <li class="column done-column">
      <div class="column-header">
        <h4>Done</h4>
      </div>
      <ul class="task-list" id="done">
        <li class="task">
          <p>Ideation</p>
        </li>
        <li class="task">
          <p>Sketches</p>
        </li>
      </ul>
    </li>

    <li class="column trash-column">
      <div class="column-header">
        <h4>Trash</h4>
      </div>
      <ul class="task-list" id="trash">
        <li class="task">
          <p>Interviews</p>
        </li>
        <li class="task">
          <p>Research</p>
        </li>

      </ul>
      <div class="column-button">
        <button class="button delete-button" onclick="emptyTrash()">Delete</button>
      </div>
    </li>

  </ul>
</div>

<!-- Scripts for Drag and Drop -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.js"></script>

<script>
        /* Custom Dragula JS */
    dragula([
    document.getElementById("to-do"),
    document.getElementById("doing"),
    document.getElementById("done"),
    document.getElementById("trash")
    ]);
    removeOnSpill: false
    .on("drag", function(el) {
        el.className.replace("ex-moved", "");
    })
    .on("drop", function(el) {
        el.className += "ex-moved";
    })
    .on("over", function(el, container) {
        container.className += "ex-over";
    })
    .on("out", function(el, container) {
        container.className.replace("ex-over", "");
    });

    /* Vanilla JS to add a new task */
    function addTask() {
    /* Get task text from input */
    var inputTask = document.getElementById("taskText").value;
    /* Add task to the 'To Do' column */
    document.getElementById("to-do").innerHTML +=
        "<li class='task'><p>" + inputTask + "</p></li>";
    /* Clear task text from input after adding task */
    document.getElementById("taskText").value = "";
    }

    /* Vanilla JS to delete tasks in 'Trash' column */
    function emptyTrash() {
    /* Clear tasks from 'Trash' column */
    document.getElementById("trash").innerHTML = "";
    }

</script>
</body>
</html>
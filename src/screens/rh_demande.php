<?php

?>

<form id="leaveForm">
        <label for="fullname">Nom complet:</label>
        <input type="text" id="fullname" name="fullname" required><br><br>

        <label for="leaveType">Type de congé:</label>
        <select id="leaveType" name="leaveType" required>
            <option value="">Sélectionner...</option>
            <option value="sortie">Sortie (2 heures)</option>
            <option value="conge">Congé</option>
        </select><br><br>

        <div id="hours" style="display:none;">
            <label for="hours">Heures de sortie:</label>
            <input type="text" id="hoursInput" name="hours"><br><br>
        </div>

        <div id="dates" style="display:none;">
            <label for="startDate">Date de départ:</label>
            <input type="date" id="startDate" name="startDate"><br><br>

            <label for="endDate">Date de retour:</label>
            <input type="date" id="endDate" name="endDate"><br><br>
        </div>

        <label for="interim">Interimaire:</label>
        <input type="text" id="interim" name="interim" required><br><br>

        <button type="button" onclick="submitForm()">Envoyer</button>
    </form>

    <script>
        $(document).ready(function() {
            $('#leaveType').change(function() {
                if ($(this).val() === 'sortie') {
                    $('#dates').hide();
                    $('#hours').show();
                } else if ($(this).val() === 'conge') {
                    $('#hours').hide();
                    $('#dates').show();
                } else {
                    $('#hours').hide();
                    $('#dates').hide();
                }
            });
        });

        function submitForm() {
            $.ajax({
                url: 'insert.php',
                type: 'POST',
                data: $('#leaveForm').serialize(),
                success: function(response) {
                    alert('Demande enregistrée avec succès!');
                    $('#leaveForm').trigger('reset'); // Reset form fields
                    $('#hours').hide();
                    $('#dates').hide();
                },
                error: function() {
                    alert('Erreur lors de l\'enregistrement de la demande.');
                }
            });
        }
    </script>
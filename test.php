<?php
echo getcwd(); // Outputs the current working directory
// Function to generate CSV string
function generateCsvString() {
    $data = [
        ["ID", "Name", "Age"],
        [1, "John Doe", 30],
        [2, "Jane Doe", 25],
        // Add more rows as needed
    ];

    $csvString = "";

    foreach ($data as $row) {
        $csvString .= implode(",", $row) . "\n";
    }

    return $csvString;
}

// Generate CSV string
$csvContent = generateCsvString();

// Specify the file path
$filePath = "outputs/output.txt";

// Save the CSV string to a file
file_put_contents($filePath, $csvContent);

echo "File saved successfully.";

?>




<H1>Hello</H1>
<button >Imprimer</button>





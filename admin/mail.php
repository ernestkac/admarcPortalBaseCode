<?php
require_once 'assets\php\dompdf-master\src\Dompdf.php';
use Dompdf\Dompdf;

function generatePDF() {
    // instantiate and use the dompdf class
    $dompdf = new Dompdf();
    
    // Load HTML content
    $dompdf->loadHtml('<h1>Welcome to My Website</h1>');
    
    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'landscape');
    
    // Render the HTML as PDF
    $dompdf->render();
    
    // Output the generated PDF (1 = download and 0 = preview)
    $dompdf->stream("welcome.pdf", array("Attachment" => 0));
}

// Call the function when this script is requested
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    generatePDF();
}
?>
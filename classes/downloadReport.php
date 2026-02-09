<?php
class ReportFileManager{
    
	public $path = "content/";

    function __construct(){

	}
	
	public function downloadFile($fileName){
		$path = $this->path . $fileName;

		// Check if the file exists and is readable
		if (file_exists($path) && is_readable($path)) {

			// Set the headers for the file type, size, and disposition
			header("Content-Type: text/plain");
			header("Content-Length: " . filesize($path));
			header("Content-Disposition: attachment; filename=" . basename($path));
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: 0");

			// Read and output the file content
			readfile($path);

			// Delete the file after download
			unlink($path);

			// Exit the script
			exit;
		} else {
			echo "File not found or not accessible";
		}
	}

    		
	public	function generateCSVFile($outputResult,$fileName){
        
		// Create a file pointer
		$file = fopen($this->path.$fileName, "w");
	
		// Get the field metadata
		$metadata = sqlsrv_field_metadata($outputResult);
	
		// Create an array to store the column names
		$column_names = array();
	
		// Loop through the metadata array
		foreach ($metadata as $field) {
		// Get the name of each field and append it to the column names array
		$column_names[] = $field['Name'];
		}
	
		// Write the column names as the first row of the csv file
		fputcsv($file, $column_names);
	
		// Loop through the outputResult array
		while ($row = sqlsrv_fetch_array($outputResult, SQLSRV_FETCH_ASSOC)) {
			foreach ($row as $key => $value) {
				if ($value instanceof DateTime) {
	
					$value = $value->format('Y-m-d H:i:s'); // convert to yyyy-mm-dd hh:mm:ss format
				  }
				$row [$key] = trim($value." ");
				if(substr($value."", 0, 1) == "0")
					$row [$key] = trim("�".$value." ");
			}
			// Write each row to the csv file
			fputcsv($file, $row);
		}
	
		// Close the file pointer
		fclose($file);
		return $fileName;
	}
}

?>
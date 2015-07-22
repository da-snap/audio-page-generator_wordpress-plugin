<?php   
if (isset($_GET['file'])) { 
	$file = $_GET['file'] ;
	$file_parts = explode("/", $file);
	$file_name = array_pop($file_parts);
	$eFile = str_replace(" ", "%20", $file);
	if ( preg_match('/\.mp3$/',$file))  { 
		header('Content-type: application/mp3');  
		header("Content-Disposition: attachment; filename=\"$file_name\"");
		readfile($eFile); 
	} else { 
		header("HTTP/1.0 404 Not Found"); 
		echo "<h1>Error 404: File Not Found: <br /><em>$file</em></h1>"; 
	}
} 
?>

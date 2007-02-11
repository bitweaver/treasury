<?php

// get the file url from querystring
$filename = realpath($_GET['file']);

// Error: only files that are in a subdir of this script can be downloaded
$current_dir = dirname(realpath($_SERVER['SCRIPT_FILENAME']));
if($current_dir != substr(dirname($filename), 0, strlen($current_dir))) {
	die( "The requested file cannot be retrieved for security reasons.");
}

// Error: PHP files cannot be downloaded
if(strToLower(substr($filename,strlen($filename)-3, 3) == 'php')) {
	die( "The requested file cannot be retrieved for security reasons.");
}

// Error: file is not found
if(!file_exists($filename)) {
	die("The requested file could not be found");
}

// required for IE, otherwise Content-disposition is ignored
if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off'); }


// build file headers
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

// header for the content type
$ext = strToLower(substr($filename,strlen($filename)-3, 3));
if ($ext == "mp3" ) { header("Content-Type: audio/x-mp3"); } 
else if ($ext == "jpg") { header("Content-Type: image/jpeg"); }
else if ($ext == "gif") { header("Content-Type: image/gif"); }
else if ($ext == "png") { header("Content-Type: image/png"); }
else if ($ext == "swf") { header("Content-Type: application/x-shockwave-flash"); }
else if ($ext == "flv") { header("Content-Type: video/flv"); }

// and some more headers
header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".filesize($filename));

// refer to file and exit
readfile("$filename");
exit();

?>
    
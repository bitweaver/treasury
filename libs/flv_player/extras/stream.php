<?php


// this example assumes your FLV files are in the "upload" directory of your website:
$file = $_SERVER["SITE_HTMLROOT"].$_GET["file"];
$pos = (isset($_GET["pos"]))  ? intval($_GET["pos"]): 0;

header("Content-Type: video/x-flv");
header('Content-Length: ' . filesize($file));


if($pos > 0) {
	print("FLV");
	print(pack('C',1));
	print(pack('C',1));
	print(pack('N',9));
	print(pack('N',9));
}


$fh = fopen($file,"rb");
fseek($fh, $pos);
while (!feof($fh)) { print(fread($fh, filesize($file))); }
fclose($fh);


?>
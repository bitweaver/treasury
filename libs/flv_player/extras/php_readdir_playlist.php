<?php

/*
This is a sample file that reads through a directory, filters the mp3/jpg/flv 
files and builds a playlist from it. After looking through this file, you'll 
probably 'get the idea' and'll be able to setup your own directory.
*/


// search for mp3 files. set this to '.flv' or '.jpg' for the other scripts 
$filter = ".mp3";
// path to the directory you want to scan
$directory = "/songs/hiphop/";


// read through the directory and filter files to an array
@$d = dir($directory);
if ($d) { 
	while($entry=$d->read()) {  
		$ps = strpos(strtolower($entry), $filter);
		if (!($ps === false)) {  
			$items[] = $entry; 
		} 
	}
	$d->close();
	sort($items);
}


// third, the playlist is built in an xspf format
// we'll first add an xml header and the opening tags .. 
header("content-type:text/xml;charset=utf-8");

echo "<playlist version='1' xmlns='http://xspf.org/ns/0/'>\n";
echo "	<title>Sample PHP Generated Playlist</title>\n";
echo "	<info>http://www.jeroenwijering.com/</info>\n";
echo "	<trackList>\n";

// .. then we loop through the mysql array ..
for($i=0; $i<sizeof($items); $i++) {
	echo "		<track>\n";
	echo "			<title>".$items[$i]."</title>\n";
	echo "			<location>".$directory.'/'.$items[$i]."</location>\n";
	echo "		</track>\n";
}
 
// .. and last we add the closing tags
echo "	</trackList>\n";
echo "</playlist>\n";


/*
That's it! You can feed this playlist to the SWF by setting this as it's 'file' 
parameter in your HTML page.
*/

?>
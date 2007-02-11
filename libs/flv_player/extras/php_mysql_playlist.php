<?php

/*
This is a sample file that extracts a list of records from a mysql database and 
builds a playlist from it. After looking through this file, you'll probably
'get the idea' and'll be able to connect the flash player
to your own database.
*/


// first connect to database
$dbcnx = @mysql_connect("localhost","USERNAME","PASSWORD");
$dbselect = @mysql_select_db("DATABASE");
if ((!$dbcnx) || (!$dbselect)) { echo "Can't connect to database"; }


// next, query for a list of titles, files and links.
$query = "SELECT title,file,link FROM mp3_table";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());


// third, the playlist is built in an xspf format
// we'll first add an xml header and the opening tags .. 
header("content-type:text/xml;charset=utf-8");

echo "<playlist version='1' xmlns='http://xspf.org/ns/0/'>\n";
echo "	<title>Sample PHP Generated Playlist</title>\n";
echo "	<info>http://www.jeroenwijering.com/</info>\n";
echo "	<trackList>\n";

// .. then we loop through the mysql array ..
while($row = @mysql_fetch_array($result)) {
	echo "		<track>\n";
	echo "			<title>".$row['title']."</title>\n";
	echo "			<creator>".$row['author']."</creator>\n";
	echo "			<location>".$row['file']."</location>\n";
	echo "			<info>".$row['link']."</info>\n";
	echo "			<image>".$row['thumbnail']."</image>\n";
	echo "		</track>\n";
}
 
// .. and last we add the closing tags
echo "	</trackList>\n";
echo "</playlist>\n";


/*
That's it! You can feed this playlist to the SWF by setting this as it's 'file' 
parameter in your HTML.
*/

?>
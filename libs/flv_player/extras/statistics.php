<?php
/*
This is a small script you can use for tracking statistics in the flash players. 
You have to set the location of this script as the "callback" flashvar for your player first.
Second, you'll have to create an empty textfile "statistics.txt" that is writeable in the same directory as this script.
Now, the script will add a line with the title each flash player item that is either started or completely listened.
You can also save the item's file url (it's variable is $file), or, if provided in the playlist, a unique id ($id)
*/


extract($_POST, EXTR_PREFIX_SAME, "post_");

$filename = 'statistics.txt';

$somecontent = $title.": ".$state."\n";

// Let's make sure the file exists and is writable first.
if (is_writable($filename)) {

   // In our example we're opening $filename in append mode.
   // The file pointer is at the bottom of the file hence 
   // that's where $somecontent will go when we fwrite() it.
   if (!$handle = fopen($filename, 'a')) {
         echo "Cannot open file ($filename)";
         exit;
   }

   // Write $somecontent to our opened file.
   if (fwrite($handle, $somecontent) === FALSE) {
       echo "Cannot write to file ($filename)";
       exit;
   }
   
   echo "Success, wrote ($somecontent) to file ($filename)";
   
   fclose($handle);

} else {
   echo "The file $filename is not writable";
}

?>
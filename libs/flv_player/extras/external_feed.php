<?php

/*
You can use this script to pass-through a playlist from an external server to the players. 
Just insert the url to external playlist below and copy this file to your server.
You can use the flashvar "file=external_feed.php" in your HTML feed this script to the player.
*/


// build file headers
header("content-type:text/xml;charset=utf-8");
// refer to file
readfile("http://www.myserver.com/myplaylist.xml");
// that's all
exit();


?>
    
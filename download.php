<?php
session_start();
if(empty($_SESSION)){
   echo 'There is nothing specified for downloading. Please redo your action at <a href="subtitle_shift.php">Subtitle_Shift.php</a>. If this error remains, email me at ankos2@hotmail.com';
   exit;
}

$filename = str_replace(" ", "_",
 ((strlen(trim($_SESSION['sub_file_name']))==0)?
   ('Subtitles.'. ((isset($_SESSION['sub_file_ext']))?
       ($_SESSION['sub_file_ext']) : ('.unknown')))
   :($_SESSION['sub_file_name']))
 );
header("Content-Disposition: attachment; filename=".$filename);
header("Content-Type: application/force-download");
header("Content-Length: ".strlen($_SESSION['sub_file_contents']));
echo $_SESSION['sub_file_contents'];
?>
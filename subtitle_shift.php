<?php
session_start();
if(isset($_POST['submit'])){
               // $aaf = 0;
               $result = '';

   $sub['delimiter'] = $_POST['delimiter'];
   $sub['shift_period_value'] = $_POST['shift_period'];
   $sub['shift_period_type']  = $_POST['shift_type'];
   $sub['timeform_length'] = $_POST['timeform_length'];
   $sub['timeform_format'] = $_POST['timeform_format'];
   $sub['extention'] = $_POST['sub_ext'];

   if ($_POST['src_type'] == 'file'){
      $sub_old = file_get_contents($_FILES['subs_file']['tmp_name']);
      $_POST['subtitles'] = $sub_old;
      $_SESSION['sub_file_name'] = basename($_FILES['subs_file']['name']);
      $_SESSION['sub_file_type'] = $_FILES['subs_file']['type'];
      $sub['extention'] = array_pop(explode(".", $_SESSION['sub_file_name']));
      $sub['timeform_format'] = ( ($sub['extention']=='sub')? ("{%d}{%d}") : ("%02d:%02d:%02d,%03d") );
   }else{
      $sub_old = $_POST['subtitles'];
   }

   if($sub['extention'] == 'srt'){
      function transform($time){//if($GLOBALS['aaf']++<5){echo "'".$time."'<hr />";}
         $format = $GLOBALS['sub']['timeform_format'];
         sscanf($time, $format, $hours, $minutes, $seconds, $miliseconds);

         switch($GLOBALS['sub']['shift_period_type']){
            case "hours"      : case "0":
               $hours        += $GLOBALS['sub']['shift_period_value'];
               break;
            case "minutes"    : case "1":
               $minutes      += $GLOBALS['sub']['shift_period_value'];
               break;
            case "seconds"    : case "2":
               $seconds      += $GLOBALS['sub']['shift_period_value'];
               break;
            case "miliseconds": case "3":
               $miliseconds += $GLOBALS['sub']['shift_period_value'];
               break;
         }

         return sprintf($format, $hours , $minutes , $seconds , $miliseconds);
      }

            // <delimiter>'s positionS (plural)  in <string> as an array
            function striposs($delimiter, $str){
               $arr = array();
               $int = 0;

               while( ($temp = stripos($str, $delimiter, $int)) !== false){
                  $arr[] = $temp;
                  $int = $temp+1;
               }

               return $arr;
            }

      $temp_sub_old = $sub_old;
      $p = striposs($sub['delimiter'], $sub_old);
      foreach($p as $key=>$value){
         $from = transform(substr($temp_sub_old, ($value-$sub['timeform_length']<0)?0:$value-$sub['timeform_length'], $sub['timeform_length']));
         $to   = transform(substr($temp_sub_old, $value+strlen($sub['delimiter']), $sub['timeform_length']));

         $temp_sub_old  =
            substr($temp_sub_old, 0, $value-$sub['timeform_length'])
             . $from .
            $sub['delimiter']
             . $to   .
            substr($temp_sub_old, $value+strlen($sub['delimiter'])+$sub['timeform_length'])
         ;
      }
      $sub_new = $temp_sub_old;
   }else
   if($sub['extention'] == 'sub'){

      function transform($time){
         switch($GLOBALS['sub']['shift_period_type']){
            case 'hours': case '0':
               $shift_koef = 3600;
               break;
            case 'minutes': case '1':
               $shift_koef = 60;
               break;
            default:
            case 'seconds': case '2':
               $shift_koef = 1;
               break;
            case 'miliseconds': case '3':
               $shift_koef = 0;
               break;
         }
         $shift_value = $GLOBALS['sub']['shift_period_value'] * $shift_koef;

         return $time+$shift_value;
      }

      $line = explode("\n", $sub_old);
      $lines_new = array();

      foreach($line as $key=>$value){
         sscanf($value, $sub['timeform_format'], $from,$to);
         //if($key<5) echo substr($value, strlen(sprintf($sub['timeform_format'], $from, $to)))."<hr />";
         $rest = substr($value, strlen(sprintf($sub['timeform_format'], $from, $to)));
         $lines_new[$key] = sprintf($sub['timeform_format'], transform($from), transform($to)).$rest;
      }
      $sub_new = join("\n", $lines_new);
   }else
      $sub_new = $sub_old;

    $_SESSION['sub_file_contents'] = $sub_new;
    if(strlen(trim($_SESSION['sub_file_name']))==0)
      $_SESSION['sub_file_ext'] = $sub['extention'];

    $result = "<hr width='60%' />\n <big><strong>Here is the resault:</strong></big>\n<a href='download.php'>Click to download as file</a>\n<br />\n <textarea rows='15' cols='80' name='new_subtitles'>".$sub_new."</textarea>\n\n<script type='text/javascript'>window.scrollTo(0, 5000);window.location.href='download.php';</script>\n\n";
}
?>
<html>
<head>
<title> Shift a Movie Subtitles' Timing</title>

<script type="text/javascript">
   function autofill(fortype){
      var field1 = document.getElementById("delimiter");
      var field2 = document.getElementById("timeform_length");
      var field3 = document.getElementById("timeform_format");

      field1.value = ( (fortype=='sub')? ("}{") : (" --> ") );
      field1.disabled = ((fortype=='sub')? (true) : (false) );
      field2.value = ( (fortype=='sub')? (8) : (12) );
      field2.disabled = ((fortype=='sub')? (true) : (false) );
      field3.value = ( (fortype=='sub')? ("{%d}{%d}") : ("%02d:%02d:%02d,%03d") );
   }

   function toggle_advanced(){
      if(document.getElementById("adv_btn").checked)
         document.getElementById("adv_cont").style.display = "inline";
      else
         document.getElementById("adv_cont").style.display = "none";
   }

   function toggle_src_type(t){
      if(t=='file'){
         document.getElementById("src_type_file").style.display = "inline";
         document.getElementById("src_type_code").style.display = "none";
         document.getElementById("file_ext_choice").style.display = "none";
         document.getElementById("file_ext_choice_1").style.display = "none";
         document.getElementById("file_ext_choice_2").style.display = "none";
      }else{
         document.getElementById("src_type_file").style.display = "none";
         document.getElementById("src_type_code").style.display = "inline";
         document.getElementById("file_ext_choice").style.display = "inline";
         document.getElementById("file_ext_choice_1").style.display = "inline";
         document.getElementById("file_ext_choice_2").style.display = "inline";
      }
   }
</script>

<style type="text/css">
input{text-align: center;}
</style>

</head>
<body>

<table><tr><td width="650">
This is a tool which will help you do that tiny little adjustment when the subtitles of a movie are a little bit faster or slower than they should.
<br /><br />By default there are two subtitle file extensions which this tool supports.
<br />In case your subtitles are different from both, feel free to use the <b>advanced</b> settings. But be sure you know what you do, a wrong value might output giberish! (Yet you lose <b>nothing</b> at all...)
</td></tr></table>

<br />
<form action="subtitle_shift.php" method="POST" ENCTYPE="multipart/form-data">

   Input your subtitles as: <br />
   <label><input type="radio" name="src_type" onchange="toggle_src_type('file');" value="file" checked />File</label>
   <label><input type="radio" name="src_type" onchange="toggle_src_type('code');" value="code" />Code</label>
   <br /><br />

      <label id="src_type_file">
         Please select your <em>Subtitles' <strong>File</strong></em><br />
         <input type="file" name="subs_file" />
      </label>

<?php
 if(isset($_POST['subtitles'])){
    echo '<br /><br />'."\n";
    echo '      <label id="src_type_code">'."\n";
    echo '         <big><strong>Your input:</strong></big><br />'."\n";
    echo '         <textarea rows="15" cols="80" name="subtitles">';
    echo $_POST['subtitles']."\n";
    echo '         </textarea>'."\n";
    echo '      </label>'."\n";
 }else{
    echo '      <label id="src_type_code" style="display:none;">'."\n";
    echo '         <textarea rows="15" cols="80" name="subtitles">';
    echo "Please input the subtitles' code.\nTo get that, open your subtitle file with notepad and copy the contents\n";
    echo '         </textarea>'."\n";
    echo '      </label>'."\n";
 }
?>

   <br /><br />
   <label>
      Shift by (<i>value</i>)
      <input type="text" value="-1" name="shift_period" size="2" />
   </label>
   <br />
   <label>
      Shift the (<i>type</i>)&nbsp;
      <input type="text" value="seconds" name="shift_type" size="10" />
   </label>
   <br />

   <br />
   <label style="display:none;" id="file_ext_choice">According to the <b>File Extension</b><br />
      <label style="display:none;" id="file_ext_choice_1">
         <input type="radio" name="sub_ext" value="srt" onclick="autofill('srt');" checked />
         A <em>.srt</em> subtitles
      </label>
      <label style="display:none;" id="file_ext_choice_2">
         <input type="radio" name="sub_ext" value="sub" onclick="autofill('sub');" />
         A <em>.sub</em> subtitles
      </label>
   </label>
   <br />
   <br />
   <label>
      <input type="checkbox" id="adv_btn" name="advanced" onchange="toggle_advanced();" />
      Advanced
   </label>
   <br />
   <fieldset id="adv_cont" style="display:none;">
      <legend><em>Advanced</em></legend>

      <label>
         Delimiter
         <input type="text" name="delimiter" id="delimiter" value=" --> " size="5" />
      </label>
      <br />
      <label>
         Timeform Length
         <input type="text" name="timeform_length" id="timeform_length" value="12" size="2" />
      </label>
      <br />
      <label>
         Timeform Format
         <input type="text" name="timeform_format" id="timeform_format" value="%d:%d:%d,%d" />
      </label>
   </fieldset>
   <br />
   <input type="submit" name="submit" value="Shift!" />
</form>
   <?php echo $result; ?>
</body>
</html>
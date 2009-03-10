<?php

require_once ('./config.php');

global $JAVASCRIPT_BASE;
global $ASSETS_BASE;

?>

<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">
<html> <head>
<meta content="text/html; charset=utf-8" http-equiv="content-type">
<script src="<?php echo $JAVASCRIPT_BASE ?>/MochiKit/MochiKit.js" type="text/javascript"></script>
<script src="<?php echo $JAVASCRIPT_BASE ?>/ajaxify_forms.js" type="text/javascript"></script>

<title>Administer Wikispace</title>

<style type="text/css">
  body { background-color:#e7edee; font-family: arial,helvetica,sans-serif; font-size: 83%; margin:0; }

  #masthead { width: 100%; height: 30px; background-color: #333366; background-image: url(<?php echo $ASSETS_BASE ?>/topgrad.jpg); background-repeat: repeat x; color: fff;}
  #masthead a { color: #fff; font-size: 12px; line-height: 26px;}
  #masthead img {border: none;}
  #masthead img {border: none;}
  #courseadmin {margin: 30};

  a { text-decoration: none; color: #006699; }
</style>
 
</head>

<body>

<div id="masthead"><a href="http://wikispaces.columbia.edu/"><img src="<?php echo $ASSETS_BASE ?>/wikispaces_cu_logo.jpg" alt="CU Wiki" name="cu_logo" id="cu_logo" /></a></div>

   <div id="main_content">
   <div id="courseadmin">
	<!--a href="/course/;add_form" >Add a Course</a-->
	<span id="addcourselink" class="linkheading">List Wikispaces Members</span>
	<div id="courseform">
	  <form action="./ws_list_members.php" method="GET" class="tableform" name="list_members" id="list_members">
        <table border="0" cellpadding="2" cellspacing="0" bgcolor="#FFFFFF">
            <tr class="even">
                <th>

                    <label class="fieldlabel" for="space_name">space</label>
                </th>
                <td>
                    <input class="textfield" type="text" id="space_name" name="space_name">
                </td>
            </tr>
            <tr>
                <td> </td>
                <td><input type="submit" class="submitbutton" value="List"></td>
            </tr>
        </table>
    </form>
	</div>
	<div id=list_members_results>
            <div class="unknown" id=list_members_message></div>
        </div>
      </div>
      </div>



<hr>

<form>

</form>

</body> </html>

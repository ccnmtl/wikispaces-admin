<?php

require_once("ws_soap_common.php");

global $JAVASCRIPT_BASE;
global $ASSETS_BASE;
global $IMAGE_BASE;
global $LOGOUT_URL;
global $CONTACT_EMAIL;

$my_courses = getMyCourses();

$exists = spaceExists($my_courses);

$REMOTE_USER = $_SERVER['REMOTE_USER']; 
$space_name = $_REQUEST['space_name'];

$url = "https://${space_name}.wikispaces.columbia.edu";

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

  div.inprogress { background: #37456C url(<?php echo $IMAGE_BASE ?>/spinner.gif) no-repeat 0px 0px; padding-left: 22px; color: #FFF}

</style>
 
</head>

<body>

<div id="masthead"><a href="http://wikispaces.columbia.edu/"><img src="<?php echo $ASSETS_BASE ?>/wikispaces_cu_logo.jpg" alt="CU Wiki" name="cu_logo" id="cu_logo" /></a></div>

   <div id="main_content">
   <div id="courseadmin">
	<div id="courseform">
	You are currently logged in as <?php echo $_SERVER['REMOTE_USER'] ?>. <a href="<?php echo $LOGOUT_URL ?>">logout</a>.

	  <div id=selfjoin_results>
	        <div class="unknown" id=selfjoin_message></div>
	  </div>

	  <?php if ($space_name) { ?>

		  <?php if (isMemberOrOrganizer($space_name, $REMOTE_USER)) { ?>
			  <span id="addcourselink" class="linkheading">You are already a member of the <i><?php echo $space_name ?></i> course wiki.  Follow this link to access it: <a href="<?php echo $url ?>"><?php echo $url ?></a></span>

		  <?php } else { ?>

			  <div id="<?php echo $space_name ?>-action">
			    <span id="addcourselink" class="linkheading">Please click the 'Join' button to become a member of the <i><?php echo $space_name ?></i> course wiki.</span>

			    <form action="./ws_selfjoin.php" method="GET" class="tableform" name="selfjoin" id="selfjoin">
	  		     <input type="hidden" name="space_name" value="<?php echo $space_name ?>">
	  		     <input type="submit" class="submitbutton" value="Join">
			    </form>
			  </div>
		  <?php } ?>

	  <?php } else { ?>	    
	    <?php if (count($my_courses) == 0) { ?>
		      <p><b>Sorry, but you are not officially affiliated with any courses. Please contact <?php $CONTACT_EMAIL ?> for more assistance.</b>
	    <?php } else { ?>
		      <table border="1" cellpadding="2" cellspacing="0" bgcolor="#FFFFFF">
		        <tr>
		          <th>Course Key</th>
		          <th>Wiki</th>
		        </tr>
		      <?php foreach ($my_courses as $course) { ?>
			<tr class="even">
			  <td><?php echo $course ?></td>
			
			  <?php if (! $exists[$course]) { ?>
				    <td><div>
				        Your Instructor has not set up a wikispace for this class.  
					</div>
				    </td>
							      
   		          <?php } else { ?>
				  <?php if (isMemberOrOrganizer($course, $REMOTE_USER)) { ?>
				    <td><div id="<?php echo $course ?>-action">
				           You are a member of <a href="http://<?php echo $course ?>.wikispaces.columbia.edu">http://<?php echo $course ?>.wikispaces.columbia.edu</a>
					 </div>
				    </td>
				  <?php } else { ?>
				    <td><div id="<?php echo $course ?>-action">
				          <form action="./ws_selfjoin.php" method="GET" name="selfjoin" id="selfjoin">
					  <input type="hidden" name="space_name" value="<?php echo $course ?>">
					  <input type="submit" class="submitbutton" value="Click to join"></form>
 				         </div>
				     </td>
				  <?php } ?> <!-- matches isMemberOrOrganizer -->
		           <?php } ?> <!-- matches ! exists[$course] -->
			 </tr>
		        <?php } ?>  <!-- matches foreach my_courses -->
                        </table>
             <?php } ?> <!-- matches if count(my_courses) -->
         <?php } ?> <!-- matches if space_name -->

      </div>
      </div>

</body> </html>

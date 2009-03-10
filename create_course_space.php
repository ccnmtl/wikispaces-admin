<?php

require_once("ws_soap_common.php");

global $JAVASCRIPT_BASE;
global $ASSETS_BASE;
global $IMAGE_BASE;
global $SELFJOIN_URL;
global $LOGOUT_URL;
global $CONTACT_EMAIL;

$my_instructor_courses = getMyInstructorCourses();

$exists = spaceExists($my_instructor_courses);

?>

<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">
<html> <head>
<meta content="text/html; charset=utf-8" http-equiv="content-type">
<script src="<?php echo $JAVASCRIPT_BASE ?>/MochiKit/MochiKit.js" type="text/javascript"></script>
<script src="<?php echo $JAVASCRIPT_BASE ?>/ajaxify_forms.js" type="text/javascript"></script>
<script src="<?php echo $JAVASCRIPT_BASE ?>/display_info.js" type="text/javascript"></script>

<title>Administer Wikispace</title>
 
<style type="text/css">
  body { background-color:#e7edee; font-family: arial,helvetica,sans-serif; font-size: 83%; margin:0; }

  #masthead { width: 100%; height: 30px; background-color: #333366; background-image: url(<?php echo $ASSETS_BASE ?>/topgrad.jpg); background-repeat: repeat x; color: fff;}
  #masthead a { color: #fff; font-size: 12px; line-height: 26px;}
  #masthead img {border: none;}
  #masthead img {border: none;}
  #courseadmin {margin: 30};
  #courseInfo {float: left};
  
  a { text-decoration: none; color: #006699; }

  div.inprogress { background: #37456C url(<?php echo $IMAGE_BASE ?>/spinner.gif) no-repeat 0px 0px; padding-left: 22px; color: #FFF}
</style>
 
</head>

<body>

<div id="masthead"><a href="http://wikispaces.columbia.edu/"><img src="<?php echo $ASSETS_BASE ?>/wikispaces_cu_logo.jpg" alt="CU Wiki" name="cu_logo" id="cu_logo" /></a></div>

   <div id="main_content">
   <div id="courseadmin">
	<!--a href="/course/;add_form" >Add a Course</a-->
	<span id="addcourselink" class="linkheading">Create a Course Wikispace</span>
	<div id="courseform">
	You are currently logged in as <?php echo $_SERVER['REMOTE_USER'] ?>. <a href="<?php echo $LOGOUT_URL ?>">logout</a>.
	<div id=createspace_results>
	     <div class="unknown" id=createspace_message></div>
	</div>

	<div id=list_members_results>
	     <div class="unknown" id=list_members_message></div>
	</div>

	<?php if (count($my_instructor_courses) == 0) { ?>
		<p><b>Sorry, but you are not officially listed as the instructor for any courses. Please contact <? $CONTACT_EMAIL ?> for more assistance.</b>
	<?php } else { ?>
        <table border="1" cellpadding="2" cellspacing="0" bgcolor="#FFFFFF">
        	<tr>
		        <th>Course Key</th>
		        <th>Wiki Link</th>
		        <th>Info</th>
		        <!-- <th>List Members</th> -->
		</tr>
            <?php foreach ($my_instructor_courses as $course) { ?>
	            <tr class="even">
			<td><?php echo $course ?></td>
		
			<?php if ($exists[$course]) { ?>
        	        	<td><div id="<?php echo $course ?>-action">
				    <a href="http://<?php echo $course ?>.wikispaces.columbia.edu"><?php echo $course ?>.wikispaces.columbia.edu</a>
				    </div>
        		        </td>
			<?php } else { ?>
	        	        <td><div id="<?php echo $course ?>-action">
					 <form action="./ws_createspace.php" method="GET" name="createspace" id="createspace"> 
					<input type="hidden" name="space_name" value="<?php echo $course ?>">
					<input type="submit" class="submitbutton" value="Create">
					</form>
				    </div>
				</td>
			<?php } ?>
	                <td>&nbsp;<div id="<?php echo $course ?>-info-link" style="display : <?php echo ($exists[$course]) ? 'block' : 'none'?>">
	<a href="#" onClick="javascript:return toggleInfo(this, '<?php echo $course ?>-info');" ><strong>info</strong></a>
			</div>			
			</td>
			<!-- for testing  
			<td>
			<form action="./ws_list_members.php" method="GET" name="list_members" id="list_members"> 
					<input type="hidden" name="space_name" value="<?php echo $course ?>">
					<input type="submit" class="submitbutton" value="List Members">
			</form>
			</td>	
			-->
			
   	      </tr>
	    <?php } ?>
            <tr>
            </tr>
      </table>
      <?php } ?>
	</div>
            <?php foreach ($my_instructor_courses as $course) { 
		$course_url = "https://${course}.wikispaces.columbia.edu";
		$selfjoin_url = "$SELFJOIN_URL?space_name=${course}";
		?>
		<div  style="display: none" id="<?php echo $course ?>-info" class="courseInfo"> 
		  <p>The wiki for <i><?php echo $course ?></i> is located at: <a href="<?php echo $course_url ?>"><?php echo $course_url ?></a><br>
<p>You have been registered as the "organizer" of this wiki space, which gives you permissions to make changes and 
invite new participants.<br>
Your students must first register in order to access the wiki. Please copy and paste the notice below to an appropriate 
location in your CourseWorks site<br> or email to to your students so that they are able 
to access the registration page and the wiki itself.</p>
	   <p/>

		  <table width="400" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
            <tr>
              <td><p>Dear <?php echo $course ?> students,</p>
              <p>Our class wiki is located at:   <?php echo $course_url ?></p>
              <p>Prior to visiting the wiki, please join the wiki via this page: <?php echo $selfjoin_url ?></p>
	      <p>Wiki assignments will be posted regularly throughout the semester, so please bookmark the wiki's location (<?php echo $course_url ?>).</p>
              <p>&nbsp; </p></td>
            </tr>
          </table>
		  <p>If you plan to copy and paste the annoucement into courseworks, please feel
  free to use the following HTML-basaed message. </p>
<table width="400" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
            <tr>
              <td><p>Dear <?php echo $course ?> students, &lt;br&gt; </p>
                <p>Our class wiki is located at: &lt;a href=&quot;<?php echo $course_url ?>&quot;&gt;<?php echo $course_url ?>&lt;/a&gt;&lt;br&gt;</p>
                <p>Prior to visiting the wiki, please join the wiki via this
                  page: &lt;a
                href=&quot;<?php echo $selfjoin_url ?>&quot;&gt;<?php echo $selfjoin_url ?>&lt;/a&gt;&lt;br&gt;</p>
	            <p>Wiki assignments will be posted regularly throughout the semester, so please bookmark the wiki's location (&lt;a
                href=&quot;<?php echo $course_url ?>&quot;&gt;<?php echo $course_url ?>&lt;/a&gt;).</p>
              <p>&nbsp; </p></td>
            </tr>
</table>
		  
		  
		  <p><br>
	          </p>
		</div>
	    <?php } ?>
  </div>
</div>


		
</body> 
</html>

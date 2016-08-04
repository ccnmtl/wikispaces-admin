<?php
/* 
 * handle requests from NCW and direct the user to their coure wiki
 * Requests will come in from NCW looking like:
 * https://www1.columbia.edu/sec-cgi-bin/ccnmtl/projects/wikispaces-admin/courseworks_wikispaces.php?CRSE=20111ENGL0850Z001 
 * 
 */

require_once("ws_soap_common.php");

global $SELFJOIN_BASE_URL;
global $CREATE_SPACE_URL;

$username = $_SERVER['REMOTE_USER'];

function directorykey2space($key) {
	 $year = substr($key, 0, 4);
	 $term = substr($key, 4, 1);
	 $dept_code = substr($key, 5, 4);
	 $course_number = substr($key, 9, 4);
	 $prefix = substr($key, 13, 1);
	 $section = substr($key, 14, 3);

	 $space_name = "${dept_code}${prefix}${course_number}-${section}-${year}-${term}";
	 $ws_safe = str_replace("&", "6", $space_name);
         return strtolower($ws_safe);
}

// Make sure that the CRSE parameter is valid, or else send them to the main wikispaces home page
if (!isset($_GET['CRSE']) || 
   strlen($_GET['CRSE']) != 17) {
   $location = 'Location: https://invalid-course-key.wikispaces.columbia.edu/';	
} else  {  // The CRSE parameter looks valid, so process
   $CRSE = $_GET['CRSE'];
   $space_name = directorykey2space($CRSE);

   $space_name_arr[] = $space_name;
   $exists_arr = spaceExists($space_name_arr);
   // print var_dump("$exists_arr <br>");
   // Does this space exist? (this function takes an array)
   if ($exists_arr[$space_name]) { 
        // if I am already a member of this wiki, send to the wiki
        //print "$space_name Exists<br>";
	if (isMemberOrOrganizer($space_name, $username)) {
	   // print "$username is a member of $space_name<br>";
	   $location = "Location: https://$space_name.wikispaces.columbia.edu/";	
	} else { 
	   // if I am not already a member of this wiki, send to self-join
	   // print "$username is NOT a member of $space_name<br>";
	   $location = "Location: ${SELFJOIN_BASE_URL}?space_name=${space_name}";
	} 
   } else { // The space doesn't exist        
        // print "$space_name Does Not Exist<br>";

        // if I am an instructor of this class, send to create space
	$my_instr_courses = getMyInstructorCourses();
	if (in_array($space_name, $my_instr_courses)) {
	    $location = "Location: $CREATE_SPACE_URL?space_name=${space_name}";
	} else { // if am a student, send to the error page
	    $location = "Location: https://$space_name.wikispaces.columbia.edu/";	
	}
   }
}

// print "Redirect me to: $location";
header($location);

?>

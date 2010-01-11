<?php

require_once ('./ws_soap_common.php');

global $siteApi;
global $spaceApi;
global $userApi;
global $pageApi;
global $admin_name;
global $admin_password;

global $CONTACT_EMAIL;
global $NEW_USER_PASSWORD;

// The variables pamacea provides us with - see http://www.columbia.edu/acis/webdev/password-logout.html
$REMOTE_USER = $_SERVER['REMOTE_USER']; 
$USER_EMAIL = $_SERVER['USER_EMAIL']; 
$USER_AFFILIATIONS = $_SERVER['USER_AFFILIATIONS']; 

$session = $siteApi->login($admin_name, $admin_password);

$space_name = $_REQUEST['space_name'];
$url = "http://${space_name}.wikispaces.columbia.edu";

//print $space_name;
// print $session;

// How did we authenticate? - 'krb' or 'udb' for uni or other user database (htpasswd) (USER_EMAIL is empty for udb)
$auth_type = ($USER_EMAIL) ? 'krb' : 'udb'; 


// double check that I am actually allowed to create this site, based on my affils
$my_instructor_courses = getMyInstructorCourses();
if (!in_array($space_name, $my_instructor_courses)) {
	print '{ "results" : "Sorry, are not authorized to create this wikispace. Please contact '. $CONTACT_EMAIL . ' for more assistance.",
		 "created" : "false" }';
	exit;
}

// check to see if space already exists
try {
    $space = $spaceApi->getSpace($session, $space_name);
} catch (Exception $e) {
	print '{ "results" : "This wiki already exists at <a href=\"'.$url.'\">'.$url.'</a>.", 
		 "created" : "false" }';
	exit;
}

// create new space if it doesn't
$space = $spaceApi->createSpace($session, $space_name, "private");

// check if the user exists
try {
    $user = $userApi->getUser($session, $REMOTE_USER);
} catch (Exception $e) {
    // they don't exist - create a new user w/in wikispaces
    $user = $userApi->createUser($session, $REMOTE_USER, $NEW_USER_PASSWORD, $USER_EMAIL);
}

// add the faculty member as an organizer

// here, we add the user to the space (no check if they are already a member - do we need one?)
$added = $spaceApi->addOrganizer($session, $space->id, $user->id);


if ($added) {
	print '{ "results" : "This wiki has been created. Please visit <a href=\"'.$url.'\">'.$url.'</a> to login.",
		 "created" : "'. $space_name .'" }';
} else {
	print '{ "results" : "Sorry, there was an error creating' . $space->name . '. Pleace contact ' . $CONTACT_EMAIL . ' for more assistance.",
		 "created" : "false"}';
}


?>
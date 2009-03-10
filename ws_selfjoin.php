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

// the parameter passed in
$space_name = $_REQUEST['space_name'];

// How did we authenticate? - 'krb' or 'udb' for uni or other user database (htpasswd) (USER_EMAIL is empty for udb)
$auth_type = ($USER_EMAIL) ? 'krb' : 'udb'; 

// double check that I am actually allowed to join this site, based on my affils
$my_courses = getMyCourses();
if (!in_array($space_name, $my_courses)) {
	print '{ "results" : "Sorry, are not authorized to join this wikispace. Please contact the wikispaces organizer for more assistance.",
	         "joined" : "false" }';
	exit;
}


$session = $siteApi->login($admin_name, $admin_password);


$space = $spaceApi->getSpace($session, $space_name);

if (!$space->id) {
	print '{ "results" : "The wiki you have chosen has not been activated by your instructor. If you have problems accessing an active wiki, please contact ' . $CONTACT_EMAIL . ' for more assistance.",
	         "joined" : "false" }';
	exit;
}

// check if the user exists
$user = $userApi->getUser($session, $REMOTE_USER);
$new = false;

// they don't exist - create a new user w/in wikispaces
if (!$user->id) {
	$new = true;
	$user = $userApi->createUser($session, $REMOTE_USER, $NEW_USER_PASSWORD, $USER_EMAIL);
}

// here, we add the user to the space - 
// first check if they are already a member/org
// addMember will _demote_ an organizer!
$added = false;
if (!($spaceApi->isMember($session, $space->id, $user->id) || $spaceApi->isOrganizer($session, $space->id, $user->id))) {
	$added = $spaceApi->addMember($session, $space->id, $user->id);
}


if ($added) {
	print '{ "results" : "' . $user->username . ' is now a member of <i>' . $space->name . '</i>. Plese <a href=https://'. $space->name .'.wikispaces.columbia.edu>click here</a> to proceed to the wiki.",
		 "joined" : "'. $space_name .'" }';
} else {
	print '{ "results" : "Sorry, there was an error adding ' . $user->username . ' to ' . $space->name . '. Pleace contact ' . $CONTACT_EMAIL . ' for more assistance.",
	         "joined" : "false" }';
}

?>
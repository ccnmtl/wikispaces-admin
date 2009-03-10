<?php

require_once ('./ws_soap_common.php');

global $siteApi;
global $spaceApi;
global $userApi;
global $pageApi;
global $admin_name;
global $admin_password;

$session = $siteApi->login($admin_name, $admin_password);


$space_name = $_REQUEST['space_name'];
//print $space_name;
// print $session;

$space = $spaceApi->getSpace($session, $space_name);

if (!$space->id) {
	print '{ "results" : ["Sorry, we could not find the wiki you specified."], "created" : "'.$space_name .'"}';
	exit;
}

// print $space->id;
$usernames = array();
$members = $spaceApi->listMembers($session, $space->id);
// print_r ($members);
foreach ($members as $member) {
  // print $member->userid . ", " . $member->username . "<br>";
  $usernames[] = $member->username;
}

// return a simple json array 
print '{ "results" : ["'. implode('","', $usernames) . '"],
	 "created" : "'.$space_name.'" }';

?>
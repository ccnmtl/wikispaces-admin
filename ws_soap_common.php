<?php

require_once ('./config.php');
global $USERNAME;
global $PASSWORD;
global $WIKISPACES_BASE;

$admin_name = $USERNAME;
$admin_password = $PASSWORD;

$NRA_FILE = "/wwws/data/ccnmtl/access/nra/nra";

$COURSE_PREFIX = "CUcourse_";
$INSTRUCTOR_PREFIX = "CUinstr_";

// we depend on the php5 soap extension --enable-soap
$siteApi = new SoapClient($WIKISPACES_BASE . '/site/api/?wsdl');
$spaceApi = new SoapClient($WIKISPACES_BASE . '/space/api/?wsdl');
$userApi = new SoapClient($WIKISPACES_BASE . '/user/api/?wsdl');
$pageApi = new SoapClient($WIKISPACES_BASE . '/page/api/?wsdl');

function getSession() {
	global $siteApi;
	$session = $siteApi->login($username, $password);
	return $session;
}

function getMyCourses() {

	// the courses from pamacea's affiliations
	$USER_AFFILIATIONS = $_SERVER['USER_AFFILIATIONS']; 
	$affils = explode(" ", $USER_AFFILIATIONS);

	// just for debugging
	// debugging - test that known course affil are properly transformed
	$uni = $_SERVER['REMOTE_USER'];

	if ($uni == 'jb2410' || $uni == 'dbeeby') { 
	   $affils[] = 'CUcourse_MRKTB8619_001_2009_3';
	   $affils[] = 'CUcourse_A&HY5010_002_2009_1';
	}

	$my_courses = array_filter($affils, isCourse);
	$my_courses = array_filter($my_courses, isCurrent);
	// debugging TC insanity
	// $my_courses[] = "CUcourse_A&HY5010_002_2009_1";
	$my_courses = array_map(affil2space, $my_courses);

	// also, append the NRA file
	$nra_courses = getNRACoursesByUni();
	//print_r($nra_courses);
	$nra_courses = array_map(coursekey2space, $nra_courses);
	$my_courses = array_merge($my_courses, $nra_courses);

        usort($my_courses, "cmp_semester");

	if ($uni == 'jb2410' || $uni == 'dbeeby') { 
	   $my_courses[] = 'www';
	   $my_courses[] = 'ccnmtl';
	   $my_courses[] = 'jonah-sandbox';
	}

	return $my_courses;	
}

function getMyInstructorCourses() {
	$USER_AFFILIATIONS = $_SERVER['USER_AFFILIATIONS']; 
	$affils = explode(" ", $USER_AFFILIATIONS);
	// for testing
	//$affils[] = "CUinstr_COLLF2010_001_2006_1";

	$my_instr_courses = array_filter($affils, isInstructor);
	$my_instr_courses = array_filter($my_instr_courses, isCurrent);

	$my_instr_courses = array_map(affil2space, $my_instr_courses);

        usort($my_instr_courses, "cmp_semester");

	// just for debugging
	$uni = $_SERVER['REMOTE_USER'];
	if ($uni == 'jb2410' || $uni == 'dbeeby') { 
		$my_instr_courses[] = 'www';
		$my_instr_courses[] = 'ccnmtl';	
		$my_instr_courses[] = 'jonah-playground3';
		$my_instr_courses[] = 'jonah-playground4';
		$my_instr_courses[] = 'jonah-playground7';
}
	return $my_instr_courses;	
}

// returns true if s starts with CUcourse_
function isCourse($affil) {	
	global $COURSE_PREFIX;
	return (strpos($affil, $COURSE_PREFIX) === false) ? false : true;
}

// returns true if s starts with CUcourse_
function isInstructor($affil) {	
	global $INSTRUCTOR_PREFIX;
	return (strpos($affil, $INSTRUCTOR_PREFIX) === false) ? false : true;
}

// returns true if affil is in a current or future year
function isCurrent($affil) {
	$now = getdate();
	// for testing purposes, manually set this to 2006
	$now_year = $now['year'];
	// $now_year = '2006'; 
	$affil_parts = explode('_', $affil);
	$affil_year = $affil_parts[3];
	return ($affil_year >= $now_year) ? true : false;
}

// compare function for sorting spaces (not affiles! we have already chopped off the prefix by now)
//  by semester (the last number) - sort by most recent semester first (largest number first)
function cmp_semester($a, $b) {
        $a_parts = explode('-', $a);
	$a_semester = $a_parts[3];

        $b_parts = explode('-', $b);
	$b_semester = $b_parts[3];

        if ($a_semester == $b_semester) {
            // keep the classes in alphabetical order, by semester
	    return strcmp($a, $b);
        }
        return ($a_semester < $b_semester) ? 1 : -1;
}


// returns the cannonical version of a course name given an affiliation
// e.g. - CUcourse_COLLF2010_001_2006_1 -> collf2010_001_2006_1
// or     CUinstr_COLLF2010_001_2006_1 -> collf2010_001_2006_1
function affil2space($affil) {
	// find the first '_', and keep everything afterwards
	$pos = strpos($affil, "_");
	$truncated = substr($affil, $pos+1);	
	return coursekey2space($truncated);
}

// returns the cannonical version of a course name given the nra coursekey format
// e.g. - COLLF2010_001_2006_1 -> collf2010_001_2006_1
function coursekey2space($course) {
	$tmp = str_replace("_", "-", $course);

	// special case to handle TC's '&' in the course string - e.g. A&H
	// we replace the '&' with a '6', just like edblogs does.
	$ws_safe = str_replace("&", "6", $tmp);	
	return strtolower($ws_safe);
}

// takes an array of space names and returns an array whose keys are their names
// and values are true if that wiki space exists
function spaceExists($space_names) {
	global $admin_name;
	global $admin_password;
	global $siteApi;
	global $spaceApi;

	$session = $siteApi->login($admin_name, $admin_password);
	
	$exists = array();
	foreach ($space_names as $space_name) {
                try {
                    $space = $spaceApi->getSpace($session, $space_name);
                    $exists[$space_name] = true;
                } catch (Exception $e) {
                    $exists[$space_name] = false;
                }
	}	
	return $exists;
}

//
// checks if a username is already a member/organizer of this space
// returns boolean
//
function isMemberOrOrganizer($space_name, $username) {
	global $admin_name;
	global $admin_password;
	global $siteApi;
	global $spaceApi;
	global $userApi;

	$session = $siteApi->login($admin_name, $admin_password);
	
	try {
		$space = $spaceApi->getSpace($session, $space_name);
	} catch (Exception $e) {	
		return false;
	}
	try { 
	    $user = $userApi->getUser($session, $username);
	}
	catch (Exception $e) {
		return false;
	}
	// return true if this user is a member or an organizer of this space
	return ($spaceApi->isMember($session, $space->id, $user->id) || 
		$spaceApi->isOrganizer($session, $space->id, $user->id));

}

// returns the courses that the logged in user is affilated with 
// according to the special NRA file that CUIT dumps for us each night.
function getNRACoursesByUni() {
	global $NRA_FILE;
	$uni = $_SERVER['REMOTE_USER'];

	$cmd = "grep $uni $NRA_FILE";
	//print $cmd;
	// put each line into an array
	$output = explode("\n", shell_exec($cmd));

	$nra_array = array();
	foreach ($output as $line) {
		if (!$line) {
			continue;
		}	
		$nra_array[] = parseCsvLine($line);
	}

	$nra_classes = array();

	// rows in nra-unified look like this:
	// jb2410,TA,ENGLG8401_001_2009_3
	foreach ($nra_array as $nra_row) {
		$nra_classes[] = $nra_row[2];	
	}
	return $nra_classes;
}		

// php4 is braindead. here is a csv parser from http://us2.php.net/manual/en/function.fgetcsv.php
// the built in one only operates on filehanlds, and no easy way to turn a string into a stream
function parseCsvLine($str) {
        $delimier = ',';
        $qualifier = "'";
        $qualifierEscape = '\\';

        $fields = array();
        while (strlen($str) > 0) {
            if ($str{0} == $delimier)
                $str = substr($str, 1);
            if ($str{0} == $qualifier) {
                $value = '';
                for ($i = 1; $i < strlen($str); $i++) {
                    if (($str{$i} == $qualifier) && ($str{$i-1} != $qualifierEscape)) {
                        $str = substr($str, (strlen($value) + 2));
                        $value = str_replace(($qualifierEscape.$qualifier), $qualifier, $value);
                        break;
                    }
                    $value .= $str{$i};
                }
            } else {
                $end = strpos($str, $delimier);
                $value = ($end !== false) ? substr($str, 0, $end) : $str;
                $str = substr($str, strlen($value));
            }
            $fields[] = $value;
        }
        return $fields;
}

?>

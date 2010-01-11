<?php

require_once ('./config.php');
global $USERNAME;
global $PASSWORD;
global $WIKISPACES_BASE;

// we depend on the php5 soap extension --enable-soap
$siteApi = new SoapClient($WIKISPACES_BASE . '/site/api/?wsdl');
$spaceApi = new SoapClient($WIKISPACES_BASE . '/space/api/?wsdl');
$userApi = new SoapClient($WIKISPACES_BASE . '/user/api/?wsdl');
$pageApi = new SoapClient($WIKISPACES_BASE . '/page/api/?wsdl');
// $pageApi->setOpt('timeout', 30);

$session = $siteApi->login($USERNAME, $PASSWORD);

$space = $spaceApi->getSpace($session, 'www');

print "Space Name is " . $space->name . "<br>";
print "Space id is " . $space->id . "<br>";

// a known existing user
$user = $userApi->getUser($session, 'jonah_ccnmtl_columbia_edu');

print "Username is " . $user->username . "\n";

try {
    $user = $userApi->getUser($session, 'idontexist');
} catch (Exception $e) {
  print "idontexist doesn't exist\n";
   
}


$pages = $pageApi->listPages($session, $space->id);
foreach ($pages as $page) {
  print $page->name . "\n";
}

$members = $spaceApi->listMembers($session, $space->id);
print "<hr>Members:<br>";
foreach ($members as $member) {
  print $member->username . "\n";
}

?>
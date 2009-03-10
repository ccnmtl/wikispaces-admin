<?php

require_once ('./config.php');
global $USERNAME;
global $PASSWORD;
global $WIKISPACES_BASE;


$usePearSoap = true;
//$usePearSoap = false;

if ($usePearSoap) {
  require_once('SOAP/WSDL.php');
  $WSDL = new SOAP_WSDL($WIKISPACES_BASE . '/site/api/?wsdl');
  $siteApi = $WSDL->getProxy();
  $WSDL = new SOAP_WSDL($WIKISPACES_BASE . '/space/api/?wsdl');
  $spaceApi = $WSDL->getProxy();
  $WSDL = new SOAP_WSDL($WIKISPACES_BASE . '/user/api/?wsdl');
  $userApi = $WSDL->getProxy();
  $WSDL = new SOAP_WSDL($WIKISPACES_BASE . '/page/api/?wsdl');
  $pageApi = $WSDL->getProxy();
  $pageApi->setOpt('timeout', 30);
 } else {
  $siteApi = new SoapClient($WIKISPACES_BASE . '/site/api/?wsdl');
  $spaceApi = new SoapClient($WIKISPACES_BASE . '/space/api/?wsdl');
  $userApi = new SoapClient($WIKISPACES_BASE . '/user/api/?wsdl');
  //$pageApi = new SoapClient($WIKISPACES_BASE . '/page/api/?wsdl');
 }

$session = $siteApi->login($USERNAME, $PASSWORD);

$space = $spaceApi->getSpace($session, 'www');

print "Space Name is " . $space->name . "<br>";
print "Space id is " . $space->id . "<br>";

$user = $userApi->getUser($session, 'jonah_ccnmtl_columbia_edu');

print "Username is " . $user->username . "\n";


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
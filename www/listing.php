<?php
$sBasePath = realpath(__DIR__ . '/..');
require_once($sBasePath . '/conf/bootstrap.php');
require_once(Sp100pATHcLASSES . 'listing.php');

// only handle GET requests
if (!Listing::isGet()) Listing::ko();
// init and run NIC interface
Listing::so()->run();

?>

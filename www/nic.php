<?php
$sBasePath = realpath(__DIR__ . '/..');
require_once($sBasePath . '/conf/bootstrap.php');
require_once(Sp100pATHcLASSES . 'nic.php');

// only handle GET requests
if (!Nic::isGet()) Nic::ko();
// init and run NIC interface
Nic::so()->run();

?>

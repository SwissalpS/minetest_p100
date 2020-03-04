<?php
$sBasePath = realpath(__DIR__ . '/..');
require_once($sBasePath . '/conf/bootstrap.php');
require_once(Sp100pATHcLASSES . 'maping.php');

// only handle GET requests
if (!Maping::isGet()) Maping::ko();
// init and run Maping interface
Maping::so()->run();

?>

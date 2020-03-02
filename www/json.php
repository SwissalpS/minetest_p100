<?php
echo '{}';
exit();
$sBasePath = realpath(__DIR__ . '/..');
require_once($sBasePath . '/conf/bootstrap.php');
require_once(Sp100pATHcLASSES . 'json.php');

// init and run JSON interface
Json::so()->run();

?>

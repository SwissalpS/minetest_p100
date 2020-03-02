<?php
if (!defined('NL')) define('NL', chr(13));
if (!defined('Sp100pATHbASE')) define('Sp100pATHbASE', realpath(__DIR__ . '/..') . '/');
if (!defined('Sp100pATHcLASSES')) define('Sp100pATHcLASSES', Sp100pATHbASE . 'classes/');
if (!defined('Sp100pATHdATA')) define('Sp100pATHdATA', Sp100pATHbASE . 'data/');
if (!defined('Sp100pATHlOGS')) define('Sp100pATHlOGS', Sp100pATHbASE . 'logs/');
if (!defined('Ip100rADIUSmAX')) define('Ip100rADIUSmAX', 15);
if (!defined('Ip100rADIUSmIN')) define('Ip100rADIUSmIN', 1);
if (!defined('Ip100xMAX')) define('Ip100xMAX', 32000);
if (!defined('Ip100xMIN')) define('Ip100xMIN', -32000);
if (!defined('Ip100yMAX')) define('Ip100yMAX', Ip100xMAX);
if (!defined('Ip100yMIN')) define('Ip100yMIN', Ip100xMIN);
if (!defined('Ip100zMAX')) define('Ip100zMAX', Ip100xMAX);
if (!defined('Ip100zMIN')) define('Ip100zMIN', Ip100xMIN);
?>

<?php
if (!defined('NL')) define('NL', chr(13));
if (!defined('Ip100rADIUSmAX')) define('Ip100rADIUSmAX', 15);
if (!defined('Ip100rADIUSmIN')) define('Ip100rADIUSmIN', 1);
if (!defined('Ip100xMAX')) define('Ip100xMAX', 32000);
if (!defined('Ip100xMIN')) define('Ip100xMIN', -32000);
if (!defined('Ip100yMAX')) define('Ip100yMAX', Ip100xMAX);
if (!defined('Ip100yMIN')) define('Ip100yMIN', Ip100xMIN);
if (!defined('Ip100zMAX')) define('Ip100zMAX', Ip100xMAX);
if (!defined('Ip100zMIN')) define('Ip100zMIN', Ip100xMIN);

require_once(Sp100pATHcLASSES . 'authentication.php');
require_once(Sp100pATHcLASSES . 'datastorage.php');


class App {

	protected $oAuth;
	protected $oData;
	protected $aID = null;

	public function app() {} // __construct

	protected function init() {

		$this->oAuth = new Authentication(Sp100pATHdATA . 'tokens');
		if (!$this->oAuth->isOK()) $this->ko(501);
		if (!$this->oAuth->isAuthenticated()) $this->ko(400);

		$this->oData = new DataStorage(Sp100pATHdATA . 'db.json');
		if (!$this->oData->isOK()) $this->ko(502);

	} // init


	public static function isAlphaNumericString($sT) {

		if (!is_string($sT)) return false;

		$sValidChars = ' _abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		for ($i = 0; $i < strlen($sT); $i++) {

			// invalid chars => fail
			if (false === strpos($sValidChars, $sT{$i})) return false;

		} // loop looking for any invalid chars

		return true;

	} // isAlphaNumericString


	static function isGet() { return 'GET' === $_SERVER['REQUEST_METHOD']; }


	public static function isInRange($iT, $iMin, $iMax) {

		return (($iT >= $iMin) and ($iT <= $iMax));

	} // isInRange


	public static function isSamePos($aP0, $aP1, $bIncludeRadius = true) {

		if ($aP0['x'] == $aP1['x']
			and $aP0['y'] == $aP1['y']
			and $aP0['z'] == $aP1['z']) {

			if (!$bIncludeRadius) return true;
			return ($aP0['r'] == $aP1['r']);
		}
		return false;

	} // isSamePos


	protected function formatBody($sOut = '') {

		return '<html><head></head><body><h1>' . $sOut . '</h1></body></html>';

	} // formatBody


	public function ko($iError = 400) {

		$this->ok('ko', $iError, false);
		exit($iError);

	} // ko


	public function ok($sBody, $iCode = 200, $bExit = true) {

		header($_SERVER['SERVER_PROTOCOL'] . ' ' . $iCode);
		echo $this->formatBody($sBody);
		if ($bExit)	exit(0);

	} // ok


	public function run() { $this->ok('ok'); }


	static function so() {

		static $oApp;
		if (!isset($oApp)) {
			$oApp = new App();
		}
		return $oApp;

	} // so (shared object)


	public function validCommands() {

		static $aCommands;
		if (!isset($aCommands)) {
			$aCommands = array();
		}
		return $aCommands;

	} // validCommands


} // class App
?>

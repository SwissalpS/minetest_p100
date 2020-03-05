<?php
require_once(Sp100pATHcLASSES . 'core.php');

class Maping extends App {

	protected $aPos;

	protected function checkRequest() {

		if (!isset($_GET['id'])) $this->ko();

		// id fleetID-engineID-areaIDofScout
		$aParts = explode('-', $_GET['id']);
		if (1 > count($aParts)) $this->ko();
		// validate ids
		if (!self::isAlphaNumericString($aParts[0])) $this->ko();
		$this->aID = array(
			'fleetID' => $aParts[0]
		);

	} // checkRequest


	protected function execute() {

		$sUserID = $this->oAuth->id();
		$sFleetID = $this->aID['fleetID'];

		$aEngines = $this->oData->engineIDs($sUserID, $sFleetID);
		if (!$aEngines) $this->ko();

		$aCurrentPosDB = array();
		$aGotoPosDB = array();
		$aBookmarksDB = array();

		foreach ($aEngines as $sEngineID) {

			$mPos = $this->oData->getPos($sUserID, $sFleetID, $sEngineID);
			if (!$mPos) continue;

			$aCurrentPosDB[$sEngineID] = $mPos;

			$mPos = $this->oData->getGotoPos($sUserID, $sFleetID, $sEngineID);
			if (!$mPos) continue;

			$sGotoPosDB[$sEngineID] = $mPos;

		} // loop all engines in fleet

		$aBookmarks = $this->oData->getFleetBookmarks($sUserID, $sFleetID);
		if ($aBookmarks) $aBookmarksDB = $aBookmarks;

		$sCurrentPosDB = json_encode($aCurrentPosDB);
		$sGotoPosDB = json_encode($aGotoPosDB);
		$sBookmarksDB = json_encode($aBookmarksDB);

		$sOut = '<div id="mapdiv" style="height:100%;"></div>
		<script>
			SssSp100.aCurrentPosDB = ' . $sCurrentPosDB . ';
			SssSp100.aGotoPosDB = ' . $sGotoPosDB . ';
			SssSp100.aBookmarksDB = ' . $sBookmarksDB . ';
			SssSp100.executeIn("mapdiv");
		</script>';

		$this->ok($sOut);

	} // execute


	protected function formatBody($sOut = '') {

		return '<html>
	<head>
		<title>Fleet ' . (isset($this->aID['fleetID']) ? $this->aID['fleetID'] : '') . '</title>
		<link rel="stylesheet" href="css/leaflet.css" />
		<script src="js/leaflet.js"></script>
		<!-- from https://github.com/ardhi/Leaflet.MousePosition -->
		<link rel="stylesheet" href="css/L.Control.MousePosition.css" />
		<script src="js/L.Control.MousePosition.js"></script>
		<script src="js/p100.js"></script>
	</head>
	<body>
		' . $sOut . '
	</body>
</html>';

	} // formatBody


	protected function init() {

		parent::init();

	} // init


	public function run() {

		$this->checkRequest();
		$this->init();
		$this->execute();
		exit(0);

	} // run


	static function so() {

		static $oApp;
		if (!isset($oApp)) {
			$oApp = new Maping();
		}

		return $oApp;

	} // so

} // Maping
?>

<?php
require_once(Sp100pATHcLASSES . 'core.php');

class Listing extends App {

	protected $aPos;

	protected function checkRequest() {

		if (!isset($_GET['id'])) $this->ko();

		// id fleetID-engineID-areaIDofScout
		$aParts = explode('-', $_GET['id']);
		if (3 !== count($aParts)) $this->ko();
		// validate ids
		if (!self::isAlphaNumericString($aParts[0])) $this->ko();
		if (!self::isAlphaNumericString($aParts[1])) $this->ko();
		if (!self::isAlphaNumericString($aParts[2])) $this->ko();
		$this->aID = array(
			'fleetID' => $aParts[0],
			'engineID' => $aParts[1],
			'areaID' => $aParts[2]
		);

	} // checkRequest


	protected function execute() {
	
		$sUserID = $this->oAuth->id();
		$sFleetID = $this->aID['fleetID'];
	
		$aFleet = $this->oData->getFleetArray($sUserID, $sFleetID);
		if (!$aFleet) $this->ko();
		$aEngines = array_keys($aFleet);
		
		$sOut = '<table>';
		
		foreach ($aEngines as $sEngineID) {
			
			$mPos = $this->oData->getPos($sUserID, $sFleetID, $sEngineID);
			if (!$mPos) continue;
			$sOut .= '<tr><td>' . $sEngineID . '</td>';
			$sOut .= '<td>' . $mPos['r'] . '</td>';
			$sOut .= '<td>' . $mPos['x'] . '</td>';
			$sOut .= '<td>' . $mPos['y'] . '</td>';
			$sOut .= '<td>' . $mPos['z'] . '</td>';
			$sOut .= '</tr>' . NL;
			
			$mPos = $this->oData->getGotoPos($sUserID, $sFleetID, $sEngineID);
			if (!$mPos) continue;
			$sOut .= '<tr><td>goto</td>';
			$sOut .= '<td>' . $mPos['r'] . '</td>';
			$sOut .= '<td>' . $mPos['x'] . '</td>';
			$sOut .= '<td>' . $mPos['y'] . '</td>';
			$sOut .= '<td>' . $mPos['z'] . '</td>';
			$sOut .= '</tr>' . NL;
		} // loop all engines in fleet
		
		$sOut .= '</table>' . NL;

		$this->ok($sOut);

	} // execute


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
			$oApp = new Listing();
		}

		return $oApp;

	} // so

} // Listing
?>

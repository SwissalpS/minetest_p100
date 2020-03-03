<?php
require_once(Sp100pATHcLASSES . 'core.php');

class Nic extends App {

	protected $aPos;

	protected function checkRequest() {

		if (!isset($_GET['c'], $_GET['id'], $_GET['p'])) $this->ko();

		// command
		if (!in_array($_GET['c'], $this->validCommands())) $this->ko();
		$this->sCommand = $_GET['c'];

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

		// radius|x|y|z
		$aParts = explode('|', $_GET['p']);
		if (4 !== count($aParts)) $this->ko();
		$iRadius = intval($aParts[0]);
		if (!self::isInRange($iRadius, Ip100rADIUSmIN, Ip100rADIUSmAX)) $this->ko();
		$iX = intval($aParts[1]);
		if (!self::isInRange($iX, Ip100xMIN, Ip100xMAX)) $this->ko();
		$iY = intval($aParts[2]);
		if (!self::isInRange($iY, Ip100yMIN, Ip100yMAX)) $this->ko();
		$iZ = intval($aParts[3]);
		if (!self::isInRange($iZ, Ip100zMIN, Ip100zMAX)) $this->ko();
		$this->aPos = array(
			'r' => $iRadius,
			'x' => $iX,
			'y' => $iY,
			'z' => $iZ
		);

	} // checkRequest


	protected function execute() {

		// single engine position name also used for fleet bookmark name
		$sName = '';
		if (isset($_GET['n']) and self::isAlphaNumericString($_GET['n'])) {
			$sName = trim($_GET['n']);
		}

		$bChanged = $this->oData->addPos(
			$this->aPos, $this->oAuth->id(), $this->aID['fleetID'],
			$this->aID['engineID'], $this->aID['areaID'], $sName);

		if ('bf' == $this->sCommand) {
			// bookmark fleet

			// need a name
			if ('' === $sName) $this->ko(404);

			if ($this->oData->bookmarkFleet($this->oAuth->id(),
				$this->aID['fleetID'], $sName)) {
				$bChanged = true;
			} else {
				$this->ko(404);
			}

		} elseif ('2bf' == $this->sCommand) {
			// move fleet to bookmark

			// need a name
			if ('' === $sName) $this->ko(404);

			if ($this->oData->toFleetBookmark($this->oAuth->id(),
				$this->aID['fleetID'], $sName)) {
				$bChanged = true;
			} else {
				$this->ko(404);
			}

		} elseif ('crf' == $this->sCommand) {
			// change radius on all fleet engines

			// need a radius
			if (!isset($_GET['r'])) $this->ko(400);
			$iRadius = intval($_GET['r']);
			if (!self::isInRange($iRadius, Ip100rADIUSmIN, Ip100rADIUSmAX)) $this->ko(400);

			if ($this->oData->changeFleetRadius($this->oAuth->id(),
				$this->aID['fleetID'], $iRadius)) {
				$bChanged = true;
			} else {
				$this->ko(404);
			}

		} elseif ('ffg' == $this->sCommand) {
			// arrange fleet in grid formation

			// need grid info
			// radius|deltaX|deltaY|deltaZ|nX|nY|nZ
			if (!isset($_GET['g'])) $this->ko(400);
			$aParts = explode('|', $_GET['g']);
			if (7 !== count($aParts)) $this->ko();
			$iRadius = intval($aParts[0]);
			if (!self::isInRange($iRadius, Ip100rADIUSmIN, Ip100rADIUSmAX)) $this->ko();
			$iDX = intval($aParts[1]);
			if (!self::isInRange($iDX, Ip100xMIN, Ip100xMAX)) $this->ko();
			$iDY = intval($aParts[2]);
			if (!self::isInRange($iDY, Ip100yMIN, Ip100yMAX)) $this->ko();
			$iDZ = intval($aParts[3]);
			if (!self::isInRange($iDZ, Ip100zMIN, Ip100zMAX)) $this->ko();
			$iNX = intval($aParts[4]);
			if (!self::isInRange($iNX, 1, Ip100xMAX)) $this->ko();
			$iNY = intval($aParts[5]);
			if (!self::isInRange($iNY, 1, Ip100yMAX)) $this->ko();
			$iNZ = intval($aParts[6]);
			if (!self::isInRange($iNZ, 1, Ip100zMAX)) $this->ko();
			$aGrid = array(
				'r' => $iRadius,
				'dX' => $iDX,
				'dY' => $iDY,
				'dZ' => $iDZ,
				'nX' => $iNX,
				'nY' => $iNY,
				'nZ' => $iNZ
			);

			if ($this->oData->fleetFormationGrid($this->oAuth->id(),
				$this->aID['fleetID'], $this->aPos, $aGrid)) {
				$bChanged = true;
			} else {
				$this->ko(404);
			}

		} // switch command

		if ($bChanged) $this->oData->save();

		// check if we need to send a goto response
		$mPos = $this->oData->getGotoPos($this->oAuth->id(), $this->aID['fleetID'],
			$this->aID['engineID']);

		$sOut = 'ok';
		if ($mPos) {
			if (!self::isSamePos($mPos, $this->aPos)) {
				$sOut .= $mPos['r'] . '|' . $mPos['x'] . '|' . $mPos['y'] . '|' . $mPos['z'];
			}
		} // got a goto pos

		$this->ok($sOut);

	} // execute


	protected function formatBody($sOut = '') {

		return $sOut;

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
			$oApp = new Nic();
		}

		return $oApp;

	} // so


	public function validCommands() {

		static $aCommands;
		if (!isset($aCommands)) {
			$aCommands = array_merge(array('cp', 'bf', '2bf', 'crf', 'ffg'), parent::validCommands());

		}
		return $aCommands;

	} // validCommands

} // Nic
?>

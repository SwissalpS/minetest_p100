<?php
class DataStorage {

	public $oJSON;
	protected $sPathFile;
	protected $bOK;

	public function dataStorage($sPath = 'db.json') {

		$this->sPathFile = $sPath;
		$this->bOK = false;
		$sJSON = @file_get_contents($sPath);
		if (false === $sJSON) $sJSON = json_encode(array());
		$this->oJSON = json_decode($sJSON, true);
		if (null !== $this->oJSON) $this->bOK = true;

	} // dataStorage


	public function isOK() { return $this->bOK; }



	public function addPos($aPos, $sUserID, $sFleetID, $sEngineID, $sAreaID, $sName = '') {

		if (!isset($this->oJSON[$sUserID])) $this->oJSON[$sUserID] = array();
		if (!isset($this->oJSON[$sUserID]['fleets']))
			$this->oJSON[$sUserID]['fleets'] = array();
		if (!isset($this->oJSON[$sUserID]['fleets'][$sFleetID]))
			$this->oJSON[$sUserID]['fleets'][$sFleetID] = array();
		if (!isset($this->oJSON[$sUserID]['fleets'][$sFleetID]['engines']))
			$this->oJSON[$sUserID]['fleets'][$sFleetID]['engines'] = array();
		if (!isset($this->oJSON[$sUserID]['fleets'][$sFleetID]['engines'][$sEngineID]))
			$this->oJSON[$sUserID]['fleets'][$sFleetID]['engines'][$sEngineID]
				= array('areaID' => $sAreaID, 'positions' => array());
		$aPositions = $this->oJSON[$sUserID]['fleets'][$sFleetID]['engines'][$sEngineID]['positions'];
		$bAdd = false;
		if (count($aPositions)) {
			$aP0 = $aPositions[0]['pos'];
			$bSamePos = (($aP0['r'] == $aPos['r']) and ($aP0['x'] == $aPos['x'])
				and ($aP0['y'] == $aPos['y']) and ($aP0['z'] == $aPos['z']));
			if ($bSamePos) {
				if ($aPositions[0]['name'] != $sName) $bAdd = true;
			} else {
				$bAdd = true;
			}
		} else {
			// first position
			$bAdd = true;
		} // if already got at least one position or not
		if ($bAdd) {
			array_unshift($aPositions, array(
				'pos' => array(
					'r' => $aPos['r'],
					'x' => $aPos['x'],
					'y' => $aPos['y'],
					'z' => $aPos['z']
				),
				'name' => $sName,
				'ts' => time()
			));
			$this->oJSON[$sUserID]['fleets'][$sFleetID]['engines'][$sEngineID]['positions']
				= $aPositions;
		} // if actually add

		return $bAdd;

	} // addPos


	public function bookmarkFleet($sUserID, $sFleetID, $sName) {

		$aIDs = $this->engineIDs($sUserID, $sFleetID);
		if (!$aIDs) return null;

		$aBookmark = array();
		foreach ($aIDs as $sEngineID) {

			$aPos = $this->getPos($sUserID, $sFleetID, $sEngineID);
			// just in case we made a mistake when manually editing json file
			if (!$aPos) continue;
			// add to bookmark hash
			$aBookmark[$sEngineID] = $aPos;

		} // loop engines

		if (!isset($aFleet['bookmarks'])) $aFleet['bookmarks'] = array();
		$this->oJSON[$sUserID]['fleets'][$sFleetID]['bookmarks'][$sName] = $aBookmark;

		return true;

	} // bookmarkFleet


	public function changeFleetRadius($sUserID, $sFleetID, $iRadius) {

		$aIDs = $this->engineIDs($sUserID, $sFleetID);
		if (!$aIDs) return null;

		foreach ($aIDs as $sEngineID) {

			$aPos = $this->getGotoPos($sUserID, $sFleetID, $sEngineID);
			// fallback to last known position
			if (!$aPos) $aPos = $this->getPos($sUserID, $sFleetID, $sEngineID);
			// just in case we made a mistake when manually editing json file
			if (!$aPos) continue;
			$aPos['r'] = $iRadius;
			$this->setGotoPos($aPos, $sUserID, $sFleetID, $sEngineID);

		} // loop engines

		return true;

	} // changeFleetRadius


	public function engineIDs($sUserID, $sFleetID) {

		$aFleet = $this->getFleetArray($sUserID, $sFleetID);
		if ((!$aFleet) or (!isset($aFleet['engines']))) return null;

		return array_keys($aFleet['engines']);

	} // engineIDs


	public function fleetFormationGrid($sUserID, $sFleetID, $aPos, $aGrid) {

		$aIDs = $this->engineIDs($sUserID, $sFleetID);
		if (!$aIDs) return null;

		$iTotal = count($aIDs);
		if (!$iTotal) return null;
		$iIndex = 0;
		$aPos_ = array('r' => $aPos['r']);

		for ($iY = 0; $iY < $aGrid['nY']; $iY++) {
			for ($iZ = 0; $iZ < $aGrid['nZ']; $iZ++) {
				for ($iX = 0; $iX < $aGrid['nX']; $iX++) {

					$aPos_['x'] = $aPos['x'] + ($iX * $aGrid['dX']);
					$aPos_['y'] = $aPos['y'] + ($iY * $aGrid['dY']);
					$aPos_['z'] = $aPos['z'] + ($iZ * $aGrid['dZ']);
					$this->setGotoPos($aPos_, $sUserID, $sFleetID, $aIDs[$iIndex]);
					$iIndex++;
					if ($iIndex == $iTotal) return true;

				} // loop x
			} // loop z
		} // loop y

		return true;

	} // fleetFormationGrid


	public function getEngineArray($sUserID, $sFleetID, $sEngineID) {

		$aFleet = $this->getFleetArray($sUserID, $sFleetID);
		if (!isset($aFleet['engines'][$sEngineID])) return null;
		return $aFleet['engines'][$sEngineID];

	} // getEngineArray


	public function getFleetArray($sUserID, $sFleetID) {

		if (!isset($this->oJSON[$sUserID]['fleets'][$sFleetID])) return null;

		return $this->oJSON[$sUserID]['fleets'][$sFleetID];

	} // getFleetArray


	public function getFleetBookmarks($sUserID, $sFleetID) {

		$aFleet = $this->getFleetArray($sUserID, $sFleetID);
		if (!isset($aFleet['bookmarks'])) return null;

		return $aFleet['bookmarks'];

	} // getFleetBookmarks


	public function getGotoPos($sUserID, $sFleetID, $sEngineID) {

		$aEngine = $this->getEngineArray($sUserID, $sFleetID, $sEngineID);
		if (!isset($aEngine['goto']['pos'])) return null;
		return $aEngine['goto']['pos'];

	} // getGotoPos


	public function getPos($sUserID, $sFleetID, $sEngineID) {

		$aEngine = $this->getEngineArray($sUserID, $sFleetID, $sEngineID);
		if (!isset($aEngine['positions'])) return null;
		if (!count($aEngine['positions'])) return null;
		if (!isset($aEngine['positions'][0]['pos'])) return null;
		return $aEngine['positions'][0]['pos'];

	} // getPos

	public function save() {

		$mRes = @file_put_contents($this->sPathFile, json_encode($this->oJSON));

	} // save


	public function setGotoPos($aPos, $sUserID, $sFleetID, $sEngineID) {

		if (!isset($this->oJSON[$sUserID]['fleets'][$sFleetID]))
			$this->oJSON[$sUserID]['fleets'][$sFleetID] = array();
		if (!isset($this->oJSON[$sUserID]['fleets'][$sFleetID]['engines']))
			$this->oJSON[$sUserID]['fleets'][$sFleetID]['engines'] = array();
		if (!isset($this->oJSON[$sUserID]['fleets'][$sFleetID]['engines'][$sEngineID]))
			$this->oJSON[$sUserID]['fleets'][$sFleetID]['engines'][$sEngineID]
				= array('areaID' => '?', 'positions' => array());
		$this->oJSON[$sUserID]['fleets'][$sFleetID]['engines'][$sEngineID]['goto'] = array(
			'pos' => array(
				'r' => $aPos['r'],
				'x' => $aPos['x'],
				'y' => $aPos['y'],
				'z' => $aPos['z']
			),
			'ts' => time()
		);

		return true;

	} // setGotoPos


	public function toFleetBookmark($sUserID, $sFleetID, $sName) {

		$aFleet = $this->getFleetArray($sUserID, $sFleetID);
		if (!isset($aFleet['bookmarks'][$sName])) return null;

		foreach ($aFleet['bookmarks'][$sName] as $sEngineID => $aPos) {

			$this->setGotoPos($aPos, $sUserID, $sFleetID, $sEngineID);

		} // loop engines

		return true;

	} // toFleetBookmark

} // DataStorage
?>

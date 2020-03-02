<?php
class Authentication {

	protected $aAuthInfo;
	protected $aTokens;
	protected $sPathFile;
	protected $bOK;

	public function authentication($sPath = Sp100pATHdATA . 'tokens') {

		$this->sPathFile = $sPath;
		$this->aTokens = array();
		$this->bOK = true;
		$aRaw = @file($sPath);
		// file does not exist -> won't authenticate
		if (false === $aRaw) return;

		foreach ($aRaw as $sToken) {

			$sToken = trim($sToken);
			if ('#' != $sToken{0}) $this->aTokens[] = $sToken;

		} // loop out all deactivated tokens and comments

	} // authentication


	public function authInfo() {

		if ($this->aAuthInfo) return $this->aAuthInfo;
		$bAuthenticated = $this->isAuthenticated();
		$this->aAuthInfo = array(
			'isAuthenticated' => $bAuthenticated
		);
		if ($bAuthenticated) {
			$this->aAuthInfo['name'] = '';
			$this->aAuthInfo['email'] = '';
			$this->aAuthInfo['id'] = trim($_GET['t']);
		} // if authenticated

		return $this->aAuthInfo;

	} // authInfo


	public function id() {

		$aAI = $this->authInfo();
		if ($aAI['isAuthenticated']) return $aAI['id'];
		return null;

	} // id


	public function isAuthenticated() {

		if (!isset($_GET['t'])) return false;
		return $this->isValidToken(trim($_GET['t']));

	} // isAuthenticated


	public function isOK() { return $this->bOK; }


	public function isValidToken($sToken) {

		if (!is_string($sToken)) return false;
		if (0 == count($sToken)) return false;
		$sValidChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		for ($i = 0; $i < strlen($sToken); $i++) {

			// invalid chars => fail
			if (false === strpos($sValidChars, $sToken{$i})) return false;

		} // loop looking for any invalid chars

		return in_array($sToken, $this->aTokens);

	} // isValidToken

} // Authentication
?>

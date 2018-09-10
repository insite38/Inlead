<?php

class install {
	private $installFile;

	public function checkInstall($moduleOrPlugName, $plugin = false) {
		if ($plugin === false)  {
			$checkPath = "modules/";
		} else {
			$checkPath = "plugin/";
		}

		$this->installFile = $checkPath.$moduleOrPlugName."/install.txt";
		if ($this->checkInstallFileAccess() === true) {
			return true;
		}
		// start install process
		$this->installGo();
		return true;
	}

	public function checkMainInstall() {
		$this->installFile = "include/install.txt";
		if ($this->checkInstallFileAccess() === true) {
			return true;
		}
		// start install process
		$this->installGo();
		return true;
	}

	public function checkInstallAllModules() {
		$dId = opendir("modules") or dir("Installation check fail, can't open modules/ for readind");
		while ($fName = readdir($dId)) {
			if (is_dir("modules/".$fName) &&  ($fName !== ".." || $fName !== ".")) $this->checkInstall($fName);
		}



	}

	private function checkInstallFileAccess() {
		if (!file_exists($this->installFile)) {
			return true;
		}

		if (!is_writable($this->installFile)) {
			$this->haltWithNoAccessToFile();
		}
		return false;
	}

	private function haltWithNoAccessToFile() {
		$haltTemplate = new template();
		$haltTemplate->file(api::setTemplate("api/install/write.access.denided.to.install.file.html"));
		$haltTemplate->assign("fileName", $this->installFile);
		$haltTemplate->out();
		exit(1);
	}

	protected function installGo() {
		// installation procedure;

		$fName = $this->installFile;
		$fId = fopen($fName, "rt");
		if ((bool)$fId === false) {
			// can't open file to read data
			echo "Error: can't open file ".$fname." to read, please, set up correct rights";
			exit(1);
		}

		// lock file
		@flock($fId, LOCK_EX+LOCK_SH);

		// starting installation \
		while ((bool)feof($fId) === false) {
			$fData = fgets($fId);
			preg_replace("/^SQL[\s]+([^\r\n]+)/ie", "\$this->sql('\\1')", $fData);

		}

		// unlock file
		@flock($fId, LOCK_UN);
		fclose($fId);

		@unlink($this->installFile);

		if (file_exists($this->installFile)) {
			$fId = @fopen($this->installFile, "w");
			@fclose($fId);
			echo "<strong>Warning: need to remove file ".$this->installFile."</strong>\n\n";
		}

		return true;
	}

	protected function sql($query) {
		global $sql;
		$sql->query($query);
		return true;
	}


}

?>
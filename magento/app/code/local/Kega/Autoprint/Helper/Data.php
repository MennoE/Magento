<?php
class Kega_Autoprint_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Check if current user has a role that is selected to print with the use of Autoprint (ActiveX).
	 *
	 * @return boolean
	 */
	public function isActiveForUser($user)
	{
		$roles = Mage::getStoreConfig('sales_pdf/autoprint/active_for_roles');
		foreach (explode(',', $roles) as $rid) {
			if (empty($rid)) {
				continue;
			}
			if (in_array($rid, $user->getRoles()) ) {
				return true;
			}
		}

        return false;
	}

	/**
	 * Upload a file to the server (in the specified sub-dir).
	 *
	 * @param array  $credentials
	 * @param string $serverDir
	 * @param string $localDir
	 * @param string $onExit
	 * @return string
	 */
	public function getHtmlScript($credentials, $serverDir, $localDir, $onExit)
	{
		$body = '<html><head><title>' . $this->__('ActiveX, downloading PDF files.') . '</title></head>' .
				'<body><pre>' . $this->__('ActiveX, downloading PDF files.') . '</pre>' .
				$this->getScript($credentials, $serverDir, $localDir, $onExit) . '</body>' .
		        '</html>';

		return $body;
    }

	/**
	 * Upload a file to the server (in the specified sub-dir).
	 *
	 * @param array  $credentials
	 * @param string $serverDir
	 * @param string $localDir
	 * @param string $onExit
	 * @return string
	 */
	public function getScript($credentials, $serverDir, $localDir, $onExit)
	{
		return $this->_getScript($credentials['host'], $credentials['user'], $credentials['password'], $serverDir, $localDir, $onExit);
    }

	/**
     * Retreive script block to put into the website to download the file (using ActiveX + batchfile).
     *
     * @param boolean $showWindow (show the command window yes or no)
     * @param string $onExit (script code to execute if Activex is started correctly.
     * @return string (complete script block)
     */
	public function _getScript($host, $user, $password, $ftpDir, $localDir, $onExit = '') {
return <<<SCRIPT
		<script type="text/javascript">
			/**
			 * Run local download script with the use ActiveXObject or alternative way for (non IE) Mozilla browsers.
			**/
			function downloadFiles() {
				try
				{
					var WshShell = new ActiveXObject("WScript.Shell");
					ret = WshShell.Run("file://c:/Kega/checkAutoprint.cmd {$host} {$user} {$password} {$ftpDir} {$localDir}", 1, false);
				}
				catch(err)
				{
					if (document.all) {
						if (err.number == -2146827859) {
							alert('U heeft uw beveiligingsinstellingen te hoog staan.\\r\\nVoeg deze site toe aan vertrouwde websites.\\r\\nEn stel in dat ActiveX Objecten mogen worden uitgevoerd.\\r\\n\\r\\nMessage from browser: ' + err.description);
						} else if (err.number == -2147024893) {
							alert('Uw systeem kan de batchfile niet starten.\\r\\nWeet u zeker dat c:\\\Kega\\\checkAutoprint.cmd bestaat?');
						} else {
							alert("Error: "  + err.description + "\\r\\n" + err.number);
						}
					} else {
						runMozillaDownload();
					}
				}
                {$onExit}
			}

			/**
			 * Alternative download way for (non IE) Mozilla browsers.
			 *
			 * @see: http://forums.mozillazine.org/viewtopic.php?f=19&t=803615&start=0
			**/
			function runMozillaDownload()
			{
				try {
					// First enable privileges needed to use the file Components.
					try {
						netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
					}
					catch(err) {
						// If other error than no privileges, throw error.
						if (err.message.indexOf('UniversalXPConnect privileges') == -1) {
							throw err;
						}

						// Explain how to set the needed privileges.
						alert('Het script werd UniversalXPConnect privileges onthouden.\\nType "about:config" in de adres balk en zet "signed.applets.codebase_principal_support" op true');
						return;
					}

					var nsILocalFile = Components.classes["@mozilla.org/file/local;1"]
										.getService(Components.interfaces.nsILocalFile);
					var nsIProcess = Components.classes["@mozilla.org/process/util;1"]
										.getService(Components.interfaces.nsIProcess);

					// Base path
					nsILocalFile.initWithPath('c:');

					// Append each step in the path
					nsILocalFile.append("Kega");
					nsILocalFile.append("checkAutoprint.cmd");

					try {
						nsIProcess.init(nsILocalFile);
					}
					catch(err) {
						// If file was already initialized, skip error handling.
						if (err.message.indexOf('(NS_ERROR_ALREADY_INITIALIZED)') == -1) {
							throw err;
						}
					}

					if (nsIProcess.isRunning) {
						alert('Het vorige print (download) process is nog actief, deze dient eerst gesloten te worden.');
						return;
					}

					var paramArray = ["{$host}", "{$user}", "{$password}", "{$ftpDir}", "{$localDir}"];
					nsIProcess.run(false, paramArray, paramArray.length);

					nsIProcess.close;
				}
				catch(err) {
					if (err.message = 'netscape is not defined') {
						alert('U dient Microsoft Internet Explorer of Firefox te gebruiken.');
						return;
					}

					// The browser already gives a message when the file can not be found, so only output other Exception messages.
					if (err.message.indexOf('(NS_ERROR_FILE_EXECUTION_FAILED)') == -1) {
						alert(err.message);
					}
				}
			}

			// Run download script after 500 miliseconds.
			setTimeout('downloadFiles();', 500);
		</script>
SCRIPT;
	}
}

<?php

class ChangePasswordAjentiDriver implements \RainLoop\Providers\ChangePassword\ChangePasswordInterface {

  /**
	 * @var string
	 */
	private $sAllowedEmails = '';

	/**
	 * @param string $sAllowedEmails
	 *
	 * @return \ChangePasswordExampleDriver
	 */
	public function SetAllowedEmails($sAllowedEmails) {
		$this->sAllowedEmails = $sAllowedEmails;
		return $this;
	}

	/**
	 * @param \RainLoop\Model\Account $oAccount
	 *
	 * @return bool
	 */
	public function PasswordChangePossibility($oAccount) {
		return $oAccount && $oAccount->Email() &&
			\RainLoop\Plugins\Helper::ValidateWildcardValues($oAccount->Email(), $this->sAllowedEmails);
	}

	/**
	 * @param \RainLoop\Model\Account $oAccount
	 * @param string $sPrevPassword
	 * @param string $sNewPassword
	 *
	 * @return bool
	 */
	public function ChangePassword(\RainLoop\Account $oAccount, $sPrevPassword, $sNewPassword) {

    // TODO: There no error messages anywhere in this method now. When something goes wrong, a descriptive error message would be nice..

    // Get e-mail parts
    $aEmailParts = explode('@', $oAccount->Email()); // TODO: check if valid e-mail?
    $sLocal = $aEmailParts[0]; // TODO: There's also a helper somewhere in the RainLoop codebase that gets these parts from the e-mail for you. I should use that, but I can't find it now.
    $sDomain = $aEmailParts[1];

    // Get Ajenti config
    $sConfigFilename = "/etc/ajenti/mail.json"; // TODO: Check for file existence
    $sExecDir = dirname(__FILE__);
    $sConfigFile = `sudo $sExecDir/api_command.sh getConfig $sConfigFilename`;
    $oConfig = json_decode($sConfigFile); // TODO: Check for decode errors

    // Check if config is valid
    if(!$oConfig || !is_object($oConfig) || !isset($oConfig->mailboxes) || !is_array($oConfig->mailboxes)) {
      return false;
    }

    // Find mailbox
    foreach($oConfig->mailboxes as &$mailbox) {

      // Check if mailbox matches
      if($mailbox->local == $sLocal && $mailbox->domain == $sDomain) {

        // TODO: There's a commit on the Ajenti repository that hashes or encodes mail-passwords, when that goes live, the code below will break. I need to check this! Both for comparing the password and setting the new password.

        // Check if previous password matches
        if($mailbox->password != $sPrevPassword) {
          return false;
        }

        // Set new password
        $mailbox->password = $sNewPassword;

        // Build new config file string
        $sNewConfig = str_replace('\'', '\\\'', json_encode($oConfig, JSON_UNESCAPED_SLASHES)); // TODO: can we escape the single quotes like that? I think it's required since we putting this in the command line in between single quotes, but I'm not sure whether this is the way to go...

        // Write config file
        `sudo $sExecDir/api_command.sh setConfig '$sNewConfig' $sConfigFilename`;

        // Reload Ajenti mail config
        $output = `sudo $sExecDir/api_command.sh reloadConfig`;

        // Check if reloading succeeded
        if(strpos($output, 'OK') !== false) {
          // Password changed!
          return true;
        } else {
          return false;
        }

      }
    }

    // Return result (mailbox not found)
		return false;
	}
}

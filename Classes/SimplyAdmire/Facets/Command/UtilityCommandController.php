<?php
namespace SimplyAdmire\Facets\Command;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;

/**
 * The Import Command Controller
 *
 * @Flow\Scope("singleton")
 */
class UtilityCommandController extends CommandController {

	/**
	 * Generate a number of random identifiers
	 *
	 * @param integer $n
	 * @return void
	 */
	public function generateRandomIdentifiersCommand($n = 10) {
		$this->outputLine('Here are your random identifiers:');
		$this->outputLine('');

		for ($i=0;$i<10;$i++) {
			$string = $this->generateRandomString(8) . '-' . $this->generateRandomString(4) . '-' . $this->generateRandomString(4) . '-' . $this->generateRandomString(4) . '-' . $this->generateRandomString(12);
			$this->outputLine($string);
		}
		$this->outputLine('');
		$this->outputLine('Make sure you always use an identifier once ;-)');

	}

	/**
	 * Helper method to create a random string
	 *
	 * @param integer $length
	 * @return string
	 */
	protected function generateRandomString($length = 10) {
		return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, $length);
	}
}
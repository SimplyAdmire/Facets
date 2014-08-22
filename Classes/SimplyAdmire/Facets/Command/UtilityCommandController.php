<?php
namespace SimplyAdmire\Facets\Command;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Utility\Algorithms;

/**
 * The Import Command Controller
 *
 * @Flow\Scope("singleton")
 */
class UtilityCommandController extends CommandController {

	/**
	 * Generate a number of random identifiers
	 *
	 * @param integer $count
	 * @return void
	 */
	public function generateRandomIdentifiersCommand($count = 10) {
		$this->outputLine('Here are your random identifiers:');
		$this->outputLine('');

		for ($i = 0; $i < 10; $i++) {
			$this->outputLine(Algorithms::generateUUID());
		}
		$this->outputLine('');
		$this->outputLine('Make sure you always use an identifier once ;-)');

	}

}
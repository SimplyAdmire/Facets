<?php
namespace SimplyAdmire\Facets\Aspects;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;
use TYPO3\Flow\Utility\Files;

/**
 * @Flow\Aspect
 */
class TypoScriptLoadingAspect {

	/**
	 * @var array
	 * @Flow\Inject(setting="contexts.default.includes.typoScripts")
	 */
	protected $additionalTypoScriptIncludes = array();

	/**
	 * @param JoinPointInterface $joinPoint
	 * @Flow\Around("method(TYPO3\Neos\Domain\Service\TypoScriptService->generateNodeTypeDefinitions())")
	 * @return string
	 */
	public function appendAdditionalTypoScript(JoinPointInterface $joinPoint) {
		$generatedTypoScript = $joinPoint->getAdviceChain()->proceed($joinPoint);

		foreach ($this->additionalTypoScriptIncludes as $additionalTypoScriptInclude) {
			$generatedTypoScript .= $this->loadAndPrepareTypoScriptFromFile($additionalTypoScriptInclude);
		}

		return $generatedTypoScript;
	}

	/**
	 * @param string $file
	 * @return string
	 */
	protected function loadAndPrepareTypoScriptFromFile($file) {
		$content = Files::getFileContents($file) . chr(10);

		$content = preg_replace_callback('/include:[ ]?(.*)/', function($match) use ($file) {
			if (substr($match[1], 0, 11) === 'resource://') {
				return $match[0];
			}

			preg_match('/^resource:\/\/(?P<PackageKey>.*)\/Private\/TypoScripts\/(?P<Path>.*)\/.*?/', $file, $matches);

			return 'include: resource://' . $matches['PackageKey'] . '/Private/TypoScripts/' . $matches['Path'] . '/' . $match[1];
		}, $content);

		return $content;
	}
}
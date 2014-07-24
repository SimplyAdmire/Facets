<?php
namespace SimplyAdmire\Facets\Command;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;

/**
 * The Import Command Controller
 *
 * @Flow\Scope("singleton")
 */
class FacetsCommandController extends CommandController {

	/**
	 * @Flow\Inject
	 * @var \SimplyAdmire\Facets\Service\ImportService
	 */
	protected $importService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Neos\Domain\Repository\DomainRepository
	 */
	protected $domainRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository
	 */
	protected $nodeDataRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Neos\Domain\Repository\SiteRepository
	 */
	protected $siteRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Repository\WorkspaceRepository
	 */
	protected $workspaceRepository;

	/**
	 * Import Document structure
	 *
	 * @return void
	 */
	public function importDocumentsCommand() {
		if ($this->importService->getDocumentResource() === NULL) {
			$this->outputLine('The documents file could not be found based on:');
			$this->outputLine('');
			$this->outputLine('     SimplyAdmire.Facets.importService.data.documents');
			exit();
		}
		try {
			$this->importService->importDocuments();
		} catch (\Exception $exception) {
			$this->outputLine('Error: During the import of the Documents file an exception occurred: %s', array($exception->getMessage()));
			exit();
		}
		$this->outputLine('Import successful.');
	}

	/**
	 * Import NodeType data
	 *
	 * @param string $nodeType
	 * @return void
	 */
	public function importNodeTypeCommand($nodeType) {
		$importedNodeTypeData = $this->importService->importNodeType($nodeType);
		if ($importedNodeTypeData === FALSE) {
			$this->outputLine('<b>Import failed</b>');
			$this->outputLine('Please make sure you have imported the documents containing the reference node using:');
			$this->outputLine('');
			$this->outputLine('     ./flow facets:importdocuments');
			$this->outputLine('');
			$this->outputLine('Also make sure the specified nodeType has the proper configuration at:');
			$this->outputLine('');
			$this->outputLine('     SimplyAdmire.Facets.importService.data.nodeTypes.' . $nodeType);
			exit();
		}
		$this->outputLine('Import successful.');
	}

	/**
	 * Import Component data
	 *
	 * @param string $component
	 * @param string $parentNodePath
	 * @return void
	 */
	public function importComponentCommand($component, $parentNodePath = NULL) {
		$componentData = $parentNodePath === NULL ? $this->importService->importComponent($component) : $this->importService->importComponent($component, $parentNodePath);
		if ($componentData === FALSE) {
			$this->outputLine('The components file or parentNodePath could not be found based on:');
			$this->outputLine('');
			$this->outputLine('     SimplyAdmire.Facets.importService.data.components.' . $component);
			$this->outputLine('');
			$this->outputLine('Please check and correct the settings. You can optionally override the parentNodePath using the --parentNodePath argument');
			exit();
		}
		$this->outputLine('Import successful.');

	}
	/**
	 * Remove all nodes, workspaces, domains and sites.
	 *
	 * @param boolean $confirmation
	 * @return void
	 */
	public function pruneCommand($confirmation = FALSE) {
		if ($confirmation === FALSE) {
			$this->outputLine('Please confirm that you really want to remove all content from the database.');
			$this->outputLine('');
			$this->outputLine('Syntax:');
			$this->outputLine('  ./flow facets:prune --confirmation TRUE');
			exit();
		}
		$this->nodeDataRepository->removeAll();
		$this->workspaceRepository->removeAll();
		$this->domainRepository->removeAll();
		$this->siteRepository->removeAll();
		$this->outputLine('Database cleared');
	}

}
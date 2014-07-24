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
	 * @param string $filename
	 * @return void
	 */
	public function importDocumentsCommand($filename = NULL) {
		if ($filename === NULL && $this->importService->getDocumentData()=== NULL) {
			$this->outputLine('The documents file could not be found based on:');
			$this->outputLine('');
			$this->outputLine('     SimplyAdmire.Facets.importService.data.documents');
			$this->outputLine('');
			$this->outputLine('Please check and correct the settings or override the settings by adding the --filename argument.');
			exit();
		}
		try {
			$this->importService->importDocuments();
		} catch (\Exception $exception) {
			$this->outputLine('Error: During the import of the file "%s" an exception occurred: %s', array($filename, $exception->getMessage()));
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
		$this->outputLine('Import successful. The nodeType is either freshly created or its properties are updated.');
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
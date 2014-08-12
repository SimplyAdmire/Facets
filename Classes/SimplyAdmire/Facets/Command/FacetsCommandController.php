<?php
namespace SimplyAdmire\Facets\Command;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Command\NodeCommandController;
use SimplyAdmire\Facets\Annotations as Facets;

/**
 * The Import Command Controller
 *
 * @Flow\Scope("singleton")
 */
class FacetsCommandController extends NodeCommandController {

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
	 * @Flow\Inject(setting="data")
	 * @var array
	 */
	protected $data = array();

	/**
	 * @Flow\Inject(setting="defaultReferenceNodePath")
	 * @var string
	 */
	protected $defaultReferenceNodePath;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Mvc\Dispatcher
	 */
	protected $dispatcher;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var boolean
	 */
	protected $verbose = TRUE;

	/**
	 * @return void
	 */
	protected function callCommandMethod() {
		parent::callCommandMethod();

		if ($this->reflectionService->isMethodAnnotatedWith(__CLASS__, $this->commandMethodName, 'SimplyAdmire\Facets\Annotations\AutoCreateChildnodes')) {
			$this->verbose = FALSE;
			$this->autoCreateChildNodesCommand();
			$this->verbose = TRUE;
			$this->outputLine('Automatically created childnodes if missing');
		}
	}

	/**
	 * Import documents, nodeTypes and Components in one call
	 *
	 * @Facets\AutoCreateChildnodes
	 * @return void
	 */
	public function importAllCommand() {
		$this->outputLine('Import documents');
		$this->importDocumentsCommand();

		foreach ($this->data['nodeTypes'] as $nodeTypeName => $nodeTypeXml) {
			$this->outputLine('Import nodetype %s', array($nodeTypeName));
			$this->importNodeTypeCommand($nodeTypeName);
		}

		foreach ($this->data['components'] as $componentName => $componentConfiguration) {
			$this->outputLine('Import component %s', array($componentName));
			$this->importComponentCommand($componentName, isset($componentConfiguration['parentNodePath']) ? $componentConfiguration['parentNodePath'] : $this->defaultReferenceNodePath);
		}
	}

	/**
	 * Import Document structure
	 *
	 * @Facets\AutoCreateChildnodes
	 * @return void
	 */
	public function importDocumentsCommand() {
		if ($this->importService->getDocumentResource() === NULL) {
			$this->outputLine('The documents file could not be found based on:');
			$this->outputLine('');
			$this->outputLine('     SimplyAdmire.Facets.data.documents');
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
	 * @Facets\AutoCreateChildnodes
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
			$this->outputLine('     SimplyAdmire.Facets.data.nodeTypes.' . $nodeType);
			exit();
		}
		$this->outputLine('Import successful.');
	}

	/**
	 * Import Component data
	 *
	 * @Facets\AutoCreateChildnodes
	 * @param string $component
	 * @param string $parentNodePath
	 * @return void
	 */
	public function importComponentCommand($component, $parentNodePath = NULL) {
		$componentData = $parentNodePath === NULL ? $this->importService->importComponent($component) : $this->importService->importComponent($component, $parentNodePath);
		if ($componentData === FALSE) {
			$this->outputLine('The components file or parentNodePath could not be found based on:');
			$this->outputLine('');
			$this->outputLine('     SimplyAdmire.Facets.data.components.' . $component);
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

	/**
	 * Outputs specified text to the console window
	 * You can specify arguments that will be passed to the text via sprintf
	 * @see http://www.php.net/sprintf
	 *
	 * @param string $text Text to output
	 * @param array $arguments Optional arguments to use for sprintf
	 * @return void
	 */
	protected function output($text, array $arguments = array()) {
		if ($this->verbose === TRUE) {
			parent::output($text, $arguments);
		}
	}

}
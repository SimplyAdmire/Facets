<?php
namespace SimplyAdmire\Facets\Service;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Domain\Service\SiteImportService;
use TYPO3\Flow\Utility\Files;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * The Import Service
 *
 * @Flow\Scope("singleton")
 */
class ImportService extends SiteImportService {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface
	 */
	protected $contextFactory;

	/**
	 * @Flow\Inject(setting="service.importService.data", package="SimplyAdmire.Facets")
	 * @var array
	 */
	protected $data;

	/**
	 * @Flow\Inject(setting="service.importService.options.defaultReferenceNodePath", package="SimplyAdmire.Facets")
	 * @var string
	 */
	protected $defaultReferenceNodePath;

	/**
	 * @Flow\Inject(setting="service.importService.options.defaultContentCollectionNodePath", package="SimplyAdmire.Facets")
	 * @var string
	 */
	protected $defaultContentCollectionNodePath;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Service\NodeTypeManager
	 */
	protected $nodeTypeManager;

	/**
	 * @return string
	 */
	public function getDocumentData() {
		$documentData = isset($this->data['documents']) ? $this->data['documents'] : NULL;
		if ($documentData !== NULL && file_exists($documentData)) {
			return $documentData;
		}
		return NULL;
	}

	/**
	 * @param $nodeType
	 * @return string
	 */
	public function getNodeTypeData($nodeType) {
		$nodeTypeData = isset($this->data['nodeTypes'][$nodeType]) ? $this->data['nodeTypes'][$nodeType] : NULL;
		if ($nodeTypeData !== NULL && file_exists($nodeTypeData)) {
			return $nodeTypeData;
		}
		return NULL;
	}

	/**
	 * @return boolean
	 * @throws \TYPO3\Flow\Package\Exception\InvalidPackageStateException
	 * @throws \TYPO3\Flow\Package\Exception\UnknownPackageException
	 */
	public function importDocuments() {
		if ($this->getDocumentData() !== NULL) {
			$this->importSitesFromFile($this->getDocumentData(), $this->createContext());
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @param string $nodeType
	 * @param string $referenceNodePath
	 * @return boolean
	 */
	public function importNodeType($nodeType, $referenceNodePath = NULL) {

		$referenceNodePath = $referenceNodePath !== NULL ? $referenceNodePath : $this->defaultReferenceNodePath;
		if ($referenceNodePath === NULL) {
			return FALSE;
		}
		$contentContext = $this->createContext();
		$nodeData = $this->getNodeTypeData($nodeType);
		if ($nodeData !== NULL) {
			$referenceNode = $contentContext->getNode($referenceNodePath);
			if (!$referenceNode instanceof NodeInterface) {
				return FALSE;
			}
			// Add content into the reference node (mapping...)
			$nodeDataXMLElement = $this->getSimpleXMLElementFromXml($this->loadXML($nodeData));
			foreach ($nodeDataXMLElement->node as $nodeXMLElement) {
				$nodeType = $this->nodeTypeManager->getNodeType((string)$nodeXMLElement->attributes()->type);
				$contentNode = $contentContext->getNodeByIdentifier((string)$nodeXMLElement->attributes()->identifier);
				if ($contentNode instanceof NodeInterface) {
					$this->importNodeProperties($nodeXMLElement, $contentNode);
				} else {
					$contentNode = $referenceNode->getPrimaryChildNode()->createSingleNode((string)$nodeXMLElement->attributes()->nodeName, $nodeType);
					$this->importNodeProperties($nodeXMLElement, $contentNode);
				}
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @return \TYPO3\TYPO3CR\Domain\Service\Context
	 */
	protected function createContext() {
		return $this->contextFactory->create(array(
			'workspaceName' => 'live',
			'invisibleContentShown' => TRUE,
			'inaccessibleContentShown' => TRUE
		));
	}

	/**
	 * Loads and returns the XML found at $pathAndFilename
	 *
	 * @param string $pathAndFilename
	 * @return string
	 * @throws \Exception
	 */
	protected function loadXml($pathAndFilename) {
		if ($pathAndFilename === 'php://stdin') {
			// no file_get_contents here because it does not work on php://stdin
			$fp = fopen($pathAndFilename, 'rb');
			$xmlString = '';
			while (!feof($fp)) {
				$xmlString .= fread($fp, 4096);
			}
			fclose($fp);

			return $xmlString;
		}
		if (!file_exists($pathAndFilename)) {
			throw new \Exception(sprintf('Could not load Content from "%s". This file does not exist.', $pathAndFilename), 1384193282);
		}
		$this->resourcesPath = Files::concatenatePaths(array(dirname($pathAndFilename), 'Resources'));

		return file_get_contents($pathAndFilename);
	}

	/**
	 * @param string $xml
	 * @return \SimpleXMLElement
	 */
	protected function getSimpleXMLElementFromXml($xml) {
		if (defined('LIBXML_PARSEHUGE')) {
			$options = LIBXML_PARSEHUGE;
		} else {
			$options = 0;
		}
		return new \SimpleXMLElement($xml, $options);
	}
}
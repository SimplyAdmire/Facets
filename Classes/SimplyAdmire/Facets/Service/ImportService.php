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
	public function getDocumentResource() {
		$documentResourceFile = isset($this->data['documents']) ? $this->data['documents'] : NULL;
		if ($documentResourceFile !== NULL && file_exists($documentResourceFile)) {
			return $documentResourceFile;
		}
		return NULL;
	}

	/**
	 * @param string $nodeType
	 * @return string
	 */
	public function getNodeTypeResource($nodeType) {
		$nodeTypeResourceFile = isset($this->data['nodeTypes'][$nodeType]) ? $this->data['nodeTypes'][$nodeType] : NULL;
		if ($nodeTypeResourceFile !== NULL && file_exists($nodeTypeResourceFile)) {
			return $nodeTypeResourceFile;
		}
		return NULL;
	}

	/**
	 * @param string $component
	 * @return string
	 */
	public function getComponentResource($component) {
		$componentResourceFile = isset($this->data['components'][$component]['resource']) ? $this->data['components'][$component]['resource'] : NULL;
		if ($componentResourceFile !== NULL && file_exists($componentResourceFile)) {
			return $componentResourceFile;
		}
		return NULL;
	}

	protected function getComponentParentNodePath($component) {
		$componentParentNodePath = isset($this->data['components'][$component]['parentNodePath']) ? $this->data['components'][$component]['parentNodePath'] : NULL;
		if ($componentParentNodePath !== NULL) {
			return $componentParentNodePath;
		}
		return NULL;
	}
	/**
	 * @return boolean
	 * @throws \TYPO3\Flow\Package\Exception\InvalidPackageStateException
	 * @throws \TYPO3\Flow\Package\Exception\UnknownPackageException
	 */
	public function importDocuments() {
		if ($this->getDocumentResource() !== NULL) {
			$this->importSitesFromFile($this->getDocumentResource(), $this->createContext());
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
		$nodeData = $this->getNodeTypeResource($nodeType);
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
	 * @param string $component
	 * @param string $parentNodePath
	 * @return boolean
	 * @throws \Exception
	 */
	public function importComponent($component, $parentNodePath = NULL) {
		$parentNodePath = $parentNodePath !== NULL ? $parentNodePath : $this->getComponentParentNodePath($component);
		if ($parentNodePath === NULL) {
			return FALSE;
		}
		$contentContext = $this->createContext();
		$nodeData = $this->getComponentResource($component);
		if ($nodeData !== NULL) {
			$parentNode = $contentContext->getNode($parentNodePath);
			if (!$parentNode instanceof NodeInterface) {
				return FALSE;
			}
			// Add content into the reference node (mapping...)
			$nodeDataXMLElement = $this->getSimpleXMLElementFromXml($this->loadXML($nodeData));
			foreach ($nodeDataXMLElement->node as $node) {
				try {
					$this->importNode($node, $parentNode);
				} catch (\Exception $exception) {
					return FALSE;
				}
			}
			return TRUE;
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
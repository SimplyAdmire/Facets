<?php
namespace SimplyAdmire\Facets\Service;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Exception;
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
	 * @Flow\Inject(setting="data")
	 * @var array
	 */
	protected $data;

	/**
	 * @Flow\Inject(setting="defaultReferenceNodePath")
	 * @var string
	 */
	protected $defaultReferenceNodePath;

	/**
	 * @Flow\Inject(setting="defaultContentCollectionNodePath")
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
	public function getSkeletonResource() {
		$skeletonResourceFile = isset($this->data['skeleton']) ? $this->data['skeleton'] : NULL;
		if ($skeletonResourceFile !== NULL && file_exists($skeletonResourceFile)) {
			return $skeletonResourceFile;
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
	 * @throws \TYPO3\Flow\Package\Exception\InvalidPackageStateException
	 * @throws \TYPO3\Flow\Package\Exception\UnknownPackageException
	 */
	public function importSkeleton() {
		if ($this->getSkeletonResource() !== NULL) {
			$this->importSitesFromFile($this->getSkeletonResource(), $this->createContext());
		}
	}

	/**
	 * @param string $nodeType
	 * @param string $referenceNodePath
	 * @throws Exception
	 * @return void
	 */
	public function importNodeType($nodeType, $referenceNodePath = NULL) {
		$referenceNodePath = $referenceNodePath !== NULL ? $referenceNodePath : $this->defaultReferenceNodePath;
		if ($referenceNodePath === NULL) {
			throw new Exception('Error: Parent node path not found');
		}
		$contentContext = $this->createContext();
		$nodeData = $this->getNodeTypeResource($nodeType);
		if ($nodeData !== NULL) {
			$referenceNode = $contentContext->getNode($referenceNodePath);
			if (!$referenceNode instanceof NodeInterface) {
				throw new Exception('Error: referenceNode is not instance of \TYPO3\TYPO3CR\Domain\Model\NodeInterface');
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
			}
		}
	}

	/**
	 * @param string $component
	 * @param string $parentNodePath
	 * @throws \Exception
	 * @return void
	 */
	public function importComponent($component, $parentNodePath = NULL) {
		$parentNodePath = $parentNodePath !== NULL ? $parentNodePath : $this->getComponentParentNodePath($component);
		if ($parentNodePath === NULL) {
			throw new Exception('Error: Parent node path not found');
		}
		$contentContext = $this->createContext();
		$nodeData = $this->getComponentResource($component);
		if ($nodeData !== NULL) {
			$parentNode = $contentContext->getNode($parentNodePath);
			if (!$parentNode instanceof NodeInterface) {
				throw new Exception('Error: parentNode is not instance of \TYPO3\TYPO3CR\Domain\Model\NodeInterface');
			}
			// Add content into the reference node (mapping...)
			$nodeDataXMLElement = $this->getSimpleXMLElementFromXml($this->loadXML($nodeData));
			foreach ($nodeDataXMLElement->node as $node) {
				try {
					$this->importNode($node, $parentNode);
				} catch (\Exception $exception) {
					throw new \Exception(sprintf('Error: During the import of the Component an exception occurred: %s', $exception->getMessage()));
				}
			}
		}
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
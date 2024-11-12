<?php

declare(strict_types=1);

namespace In2code\Femanager\Finisher;

use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class SendParametersFinisher
 */
class SendParametersFinisher extends AbstractFinisher implements FinisherInterface
{
    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * TypoScript configuration part sendPost
     *
     * @var array
     */
    protected $configuration;

    public function __construct(
        /**
         * Inject a complete new content object
         */
        protected \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject
    )
    {
    }

    /**
     * Initialize
     */
    public function initializeFinisher(): void
    {
        $this->configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $this->contentObject->start($this->user->_getProperties());
        $typoScript = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $this->configuration = empty($typoScript['plugin.']['tx_femanager.']['settings.']['new.']['sendPost.']) ?
                null : $typoScript['plugin.']['tx_femanager.']['settings.']['new.']['sendPost.'];
    }

    /**
     * Send values via curl to a third party software
     */
    public function sendFinisher(): void
    {
        if ($this->isEnabled()) {
            $curlSettings = $this->getCurlSettings();

            /** @var RequestFactory $requestFactory */
            $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
            $params = $curlSettings['params'];
            $parsedParams = [];
            parse_str((string)$params, $parsedParams);
            $requestFactory->request($curlSettings['url'], 'POST', ['form_params' => $parsedParams]);
        }
    }

    /**
     * CURL settings
     */
    protected function getCurlSettings(): array
    {
        return [
            'url' => $this->getTargetUrl(),
            'params' => $this->getData(),
        ];
    }

    /**
     * Get parameters
     *
     * @return string
     */
    protected function getData()
    {
        return $this->contentObject->cObjGetSingle((string)$this->configuration['data'], (array)$this->configuration['data.']);
    }

    protected function getTargetUrl()
    {
        $linkConfiguration = [
            'parameter' => $this->configuration['targetUrl'],
            'forceAbsoluteUrl' => '1',
            'returnLast' => 'url',
        ];
        return $this->contentObject->typoLink('dummy', $linkConfiguration);
    }

    /**
     * Check if sendPost is activated
     */
    protected function isEnabled(): bool
    {
        return $this->contentObject->cObjGetSingle(
            $this->configuration['_enable'] ?? 'TEXT',
            $this->configuration['_enable.'] ?? '0'
        ) === '1';
    }
}

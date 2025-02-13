<?php

namespace Ocode\S3Digital\Model\Product\Image;

use Magento\Catalog\Model\Product\Image\UrlBuilder as BaseUrlBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class UrlBuilder extends BaseUrlBuilder
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get image URL without cache folder
     *
     * @param string $baseFilePath
     * @param string $imageDisplayArea
     * @return string
     */
    public function getUrl(string $baseFilePath, string $imageDisplayArea): string
    {
        // Get the dynamic AWS S3 base URL from the Magento configuration
        $s3BaseUrl = $this->scopeConfig->getValue(
            'web/unsecure/base_media_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        // Ensure the file path includes 'catalog/product/' (it may be missing)
        if (strpos($baseFilePath, 'catalog/product/') === false) {
            $baseFilePath = 'catalog/product/' . ltrim($baseFilePath, '/');
        }

        // Remove '/cache/' and hash folder from the file path
        $filePath = str_replace('/cache/', '/', $baseFilePath);

        // Return the full image URL from S3
        return $s3BaseUrl . ltrim($filePath, '/');
    }
}

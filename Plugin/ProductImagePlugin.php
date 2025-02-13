<?php

namespace Ocode\S3Digital\Plugin;

use Magento\Catalog\Helper\Image as ImageHelper;
use Psr\Log\LoggerInterface;

class ProductImagePlugin
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function afterGetUrl(ImageHelper $subject, $result)
    {
        // Check if the URL contains the cache folder and remove it
        $cacheFolder = '/cache/';
        if (strpos($result, $cacheFolder) !== false) {
            $result = str_replace($cacheFolder, '/', $result);
        }

      //  $this->logger->info('Modified Image URL: ' . $result);

        return $result;
    }
}

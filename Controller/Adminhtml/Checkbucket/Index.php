<?php
namespace Ocode\S3Digital\Controller\Adminhtml\Checkbucket;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Aws\S3\S3Client;
use Psr\Log\LoggerInterface;

class Index extends Action
{
    protected $resultJsonFactory;
    protected $configWriter;
    protected $cacheTypeList;
    protected $logger;

    public function __construct(
        Context $context,
        WriterInterface $configWriter,
        JsonFactory $resultJsonFactory,
        TypeListInterface $cacheTypeList,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->configWriter = $configWriter;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->logger = $logger;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $postData = $this->getRequest()->getPostValue();


        try {
            // Configure the S3 client
            $client = new S3Client([
                'version'     => 'latest',
                'region'      => $postData['region'],
                'endpoint'    => "https://nyc3.digitaloceanspaces.com", // Use the correct endpoint
                'use_path_style_endpoint' => true, // Use path-style endpoint to avoid SSL issues
                'credentials' => [
                    'key'    => $postData['accesskey'],
                    'secret' => $postData['secretkey'],
                ],
            ]);

            // Check if the bucket is accessible
            $client->listObjects(['Bucket' => $postData['new_bucket']]);

            $message = 'Bucket is available and accessible.';
            $this->configWriter->save('s3amazon/general/validation_message', $message, 'default', 0);

            // Clear config cache
            $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);

            $response = ['success' => true];
        } catch (\Exception $e) {
            // Handle error and log the details
            $message = 'Bucket not available and accessible: ' . $e->getMessage();
            $this->configWriter->save('s3amazon/general/validation_message', $message, 'default', 0);

            // Log the error for debugging
            $this->logger->error('Error checking S3 bucket: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            // Clear config cache
            $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);

            $response = ['success' => false, 'error' => $e->getMessage()];
        }

        return $resultJson->setData($response);
    }


    protected function _isAllowed()
    {
        return true;
    }
}

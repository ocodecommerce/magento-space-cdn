<?php
namespace Ocode\S3Digital\Controller;

use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\Action\Context;
use Ocode\S3Digital\Helper\Data;
use Aws\S3\S3Client;
class Download extends \Magento\Downloadable\Controller\Download\Link
{
    protected $helper;

    public function __construct(Context $context,
        Data $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context);
    }
    protected function _processDownload($path, $resourceType)
    {
        if ($this->helper->isConfigured()) {
            if ($resourceType == \Magento\Downloadable\Helper\Download::LINK_TYPE_URL) {
                if ($this->helper->isRelevantUrl($path))
                {
                    $protected_url = $path;
                    if ($protected_url !== false)
                    {
                        $bucket = $this->helper->getBucket();
                        $timeout = $this->helper->getRequestTimeout();
                        $file =  str_replace($this->helper->getS3MediaUrl(), "", $path);
                        if(!class_exists('Aws\S3\S3Client') && !$this->helper->getS3Options())
                            return;
                        $s3Client = new S3Client($this->helper->getS3Options());
                        $cmd = $s3Client->getCommand('GetObject', [
                            'Bucket' => $bucket,
                            'Key' => $file
                        ]);

                        $request = $s3Client->createPresignedRequest($cmd, '+'.$timeout.' seconds');
                        $presignedUrl = (string)$request->getUri();

                        $this->getResponse()
                            ->setHttpResponseCode(307)
                            ->setHeader("Location", $presignedUrl);
                        $this->getResponse()->clearBody();
                        $this->getResponse()->sendHeaders();
                        return;
                    }
                }
            }
        }
        return parent::_processDownload($path, $resourceType);
    }
}

<?php
namespace Ocode\S3Digital\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\MediaStorage\Model\File\Storage;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
class Data extends AbstractHelper
{
    const S3_AMAZON_ENABLE = 's3amazon/general/enable';
    const S3_AMAZON_BUCKET = 's3amazon/general/bucket';
    const S3_AMAZON_REGION = 's3amazon/general/region';
    const S3_AMAZON_ACCESSKEY = 's3amazon/general/access_key';
    const S3_AMAZON_SECRETSKEY = 's3amazon/general/secret_key';
    const S3_AMAZON_ENDPOINTENABLED = 's3amazon/general/custom_endpoint_enabled';
    const S3_AMAZON_ENDPOINT = 's3amazon/general/custom_endpoint';
    const S3_AMAZON_REQUEST_TIMEOUT = 's3amazon/general/request_timeout';

    protected $storeManagerInterface;
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
        parent::__construct($context);
    }
    public function isS3Enabled()
    {
        return $this->scopeConfig->getValue(self::S3_AMAZON_ENABLE,ScopeInterface::SCOPE_STORE);
    }
    public function getMediaStoragePath()
    {
        return (int)$this->scopeConfig->getValue(Storage::XML_PATH_STORAGE_MEDIA);
    }
    public function checkS3Usage()
    {
            $currentStorage = $this->getMediaStoragePath();
            if($currentStorage === \Ocode\S3Digital\Model\Core\File\Storage::STORAGE_MEDIA_S3)
                return true;
            else
                return false;
    }
    public function getRequestTimeout()
    {
        return $this->scopeConfig->getValue(self::S3_AMAZON_REQUEST_TIMEOUT, ScopeInterface::SCOPE_STORE);
    }
    public function getAccessKey()
    {
        return $this->scopeConfig->getValue(self::S3_AMAZON_ACCESSKEY, ScopeInterface::SCOPE_STORE);
    }
    public function getSecretKey()
    {
        return $this->scopeConfig->getValue(self::S3_AMAZON_SECRETSKEY, ScopeInterface::SCOPE_STORE);
    }
    public function getEndpointEnabled()
    {
        return $this->scopeConfig->getValue(self::S3_AMAZON_ENDPOINTENABLED, ScopeInterface::SCOPE_STORE);
    }
    public function getEndpoint()
    {
        return $this->scopeConfig->getValue(self::S3_AMAZON_ENDPOINT, ScopeInterface::SCOPE_STORE);
    }
    public function getBucket()
    {
        return $this->scopeConfig->getValue(self::S3_AMAZON_BUCKET, ScopeInterface::SCOPE_STORE);
    }
    public function getRegion()
    {
        return $this->scopeConfig->getValue(self::S3_AMAZON_REGION, ScopeInterface::SCOPE_STORE);
    }
    public function getBaseMediaUrl()
    {
        return $this->storeManagerInterface->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA,['_secure' => true]);
    }
    public function getS3Options()
    {
        $options = [
            'version' => 'latest',
            'region' => $this->getRegion(),
            'credentials' => [
                'key' => $this->getAccessKey(),
                'secret' => $this->getSecretKey(),
            ],
        ];
        if ($this->getEndpointEnabled()) {
            if ($this->getEndpoint()) {
                $options['endpoint'] = $this->getEndpoint();
            }
        }
        if($this->getRegion() && $this->getAccessKey() && $this->getAccessKey())
            return $options;
        else
            return false;

    }
    public function isConfigured()
    {
        return $this->getAccessKey() && $this->getSecretKey();
    }
    public function getS3MediaUrl()
    {
        if($this->getRegion() && $this->getBucket())
            return "https://".$this->getBucket().".".$this->getRegion().".digitaloceanspaces.com/";
        else
            return "error";
    }
    public function isRelevantUrl($url)
    {
        preg_match_all("/^https?:\/\/.*\.amazonaws\.com\/(.*)$/", $url, $matches);
        return $matches && count($matches) > 0 && count($matches[0]) > 0;
    }
}

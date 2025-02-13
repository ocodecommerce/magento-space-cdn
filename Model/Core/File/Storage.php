<?php
namespace Ocode\S3Digital\Model\Core\File;
use Magento\MediaStorage\Model\File\Storage as FileStorage;
use Magento\MediaStorage\Helper\File\Storage as MediaStorageHelper;
use Ocode\S3Digital\Model\Core\File\Storage\S3Factory;
class Storage extends FileStorage
{
    const STORAGE_MEDIA_S3 = 2;

    protected $_fileStorageHelper;
    protected $s3StorageFactory;

    public function __construct(
        MediaStorageHelper $fileStorageHelper,
        S3Factory $s3StorageFactory
    ) {
        $this->_fileStorageHelper = $fileStorageHelper;
        $this->s3StorageFactory = $s3StorageFactory;
    }
    public function aroundGetStorageModel(FileStorage $subject, $proceed, $storage = null, array $params = [])
    {
        $storageModel = $proceed($storage, $params);
        if ($storageModel === false) {
            if (null === $storage) {
                $storage = $this->_fileStorageHelper->getCurrentStorageCode();
            }
            switch ($storage) {
                case SELF::STORAGE_MEDIA_S3:
                    $storageModel = $this->s3StorageFactory->create();
                    break;
                default:
                    return false;
            }
            if (isset($params['init']) && $params['init']) {
                $storageModel->init();
            }
        }
        return $storageModel;
    }
}
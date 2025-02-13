<?php
namespace Ocode\S3Digital\Model\Core\File\Storage\Directory;
use Magento\MediaStorage\Model\File\Storage\Directory\Database;
use Ocode\S3Digital\Helper\Data as DataHelper;
use Ocode\S3Digital\Model\Core\File\Storage\S3 as S3Storage;

class S3Database
{
    protected $helper;
    protected $storageModel;

    public function __construct(
        DataHelper $helper,
        S3Storage $storageModel
    ) {
        $this->helper = $helper;
        $this->storageModel = $storageModel;
    }
    public function aroundCreateRecursive(Database $subject, $proceed, $path)
    {
        if ($this->helper->checkS3Usage()) {
            return $this;
        }
        return $proceed($path);
    }

    public function aroundGetSubdirectories(Database $subject, $proceed, $directory)
    {
        if ($this->helper->checkS3Usage()) {
            return $this->storageModel->getSubdirectories($directory);
        }
        return $proceed($directory);
    }

    public function aroundDeleteDirectory(Database $subject, $proceed, $path)
    {
        if ($this->helper->checkS3Usage()) {
            return $this->storageModel->deleteDirectory($path);
        }
        return $proceed($path);
    }
}
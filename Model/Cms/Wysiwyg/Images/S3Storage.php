<?php
namespace Ocode\S3Digital\Model\Cms\Wysiwyg\Images;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory;
use Magento\Framework\Filesystem;
use Ocode\S3Digital\Helper\Data;

class S3Storage
{
    protected $s3helper;
    protected $coreFileStorageDb;
    protected $directory;
    protected $directoryDatabaseFactory;

    public function __construct(
        Data $s3helper,
        Database $coreFileStorageDb,
        Filesystem $filesystem,
        DatabaseFactory $directoryDatabaseFactory
    ) {
        $this->s3helper = $s3helper;
        $this->coreFileStorageDb = $coreFileStorageDb;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->directoryDatabaseFactory = $directoryDatabaseFactory;
    }

    public function beforeGetDirsCollection(Storage $subject, $path)
    {
        $this->createSubDirectories($path);
        return [$path];
    }

    public function createSubDirectories($path)
    {
        if ($this->coreFileStorageDb->checkDbUsage()) {
            $subDirectories = $this->directoryDatabaseFactory->create();
            $directories = $subDirectories->getSubdirectories($path);
            foreach ($directories as $directory) {
                $this->directory->create($directory['name']);
            }
        }
    }

    public function afterResizeFile(Storage $subject, $result)
    {
        if ($this->s3helper->checkS3Usage()) {
            $thumbnailRelativePath = $this->coreFileStorageDb->getMediaRelativePath($result);
            $this->coreFileStorageDb->getStorageDatabaseModel()->saveFile($thumbnailRelativePath);
        }
        return $result;
    }

    public function afterGetThumbsPath(Storage $subject, $result)
    {
        return rtrim($result, '/');
    }
}
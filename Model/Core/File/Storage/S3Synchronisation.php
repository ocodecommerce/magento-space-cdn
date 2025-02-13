<?php
namespace Ocode\S3Digital\Model\Core\File\Storage;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\MediaStorage\Model\File\Storage\Synchronization;

class S3Synchronisation
{
    protected $storageFactory;
    protected $mediaDirectory;

    public function __construct(
        \Ocode\S3Digital\Model\Core\File\Storage\S3Factory $storageFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->storageFactory = $storageFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }
    public function beforeSynchronize(Synchronization $subject, $relativeFileName)
    {
        $storage = $this->storageFactory->create();
        try {
            $storage->loadByFilename($relativeFileName);
        } catch (\Exception $e) {
        }
        if ($storage->getId()) {
            $file = $this->mediaDirectory->openFile($relativeFileName, 'w');
            try {
                $file->lock();
                $file->write($storage->getContent());
                $file->unlock();
                $file->close();
            } catch (FileSystemException $e) {
                $file->close();
            }
        }
        return [
            $relativeFileName,
        ];
    }
}
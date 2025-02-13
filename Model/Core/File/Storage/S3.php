<?php
namespace Ocode\S3Digital\Model\Core\File\Storage;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\S3\BatchDelete;
use Magento\Framework\DataObject;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Ocode\S3Digital\Helper\Data as DataHelper;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\MediaStorage\Helper\File\Media;
use Psr\Log\LoggerInterface;
class S3 extends DataObject
{
    protected $mediaBaseDirectory = null;
    protected $client;
    protected $helper;
    protected $storageHelper;
    protected $dirList;
    protected $mediaHelper;
    protected $logger;
    protected $objects = [];

    public function __construct(
        DataHelper $helper,
        Database $storageHelper,
        DirectoryList $dirList,
        LoggerInterface $logger,
        Media $mediaHelper
    ) {
        parent::__construct();
        $this->helper = $helper;
        $this->storageHelper = $storageHelper;
        $this->dirList = $dirList;
        $this->logger = $logger;
        $this->mediaHelper = $mediaHelper;
        if(class_exists('Aws\S3\S3Client') && $this->helper->getS3Options())
            $this->client = new S3Client($this->helper->getS3Options());
    }
    public function init()
    {
        return $this;
    }
    public function getS3Objects($path)
    {
        $prefix = $this->storageHelper->getMediaRelativePath($path);
        $prefix = rtrim($prefix, '/') . '/';
        return $this->client->listObjects([
            'Bucket' => $this->getBucket(),
            'Prefix' => $prefix,
            'Delimiter' => '/',
        ]);
    }
    public function deleteDirectory($path)
    {
        $mediaRelativePath = $this->storageHelper->getMediaRelativePath($path);
        $prefix = rtrim($mediaRelativePath, '/') . '/';
        $this->client->deleteMatchingObjects($this->getBucket(), $prefix);

    }
    public function getSubdirectories($path)
    {
        $subdirectories = [];

        $objects = $this->getS3Objects($path);

        if (isset($objects['CommonPrefixes'])) {
            foreach ($objects['CommonPrefixes'] as $object) {
                if (!isset($object['Prefix'])) {
                    continue;
                }
                $subdirectories[] = [
                    'name' => $object['Prefix'],
                ];
            }
        }
        return $subdirectories;
    }
    public function getDirectoryFiles($path)
    {
        $files = [];
        $prefix = $this->storageHelper->getMediaRelativePath($path);
        $prefix = rtrim($prefix, '/') . '/';
        $objects = $this->getS3Objects($path);
        if (isset($objects['Contents'])) {
            foreach ($objects['Contents'] as $object) {
                if (isset($object['Key']) && $object['Key'] != $prefix) {
                    $content = $this->client->getObject([
                        'Bucket' => $this->getBucket(),
                        'Key' => $object['Key'],
                    ]);
                    if (isset($content['Body'])) {
                        $files[] = [
                            'filename' => $object['Key'],
                            'content' => (string)$content['Body'],
                        ];
                    }
                }
            }
        }
        return $files;
    }
    public function loadByFilename($filename)
    {
        $fail = false;
        try {
            $object = $this->client->getObject([
                'Bucket' => $this->getBucket(),
                'Key' => $filename,
            ]);
            if ($object['Body']) {
                $this->setData('id', $filename);
                $this->setData('filename', $filename);
                $this->setData('content', (string)$object['Body']);
            } else {
                $fail = true;
            }
        } catch (S3Exception $e) {
            $fail = true;
        }
        if ($fail) {
            $this->unsetData();
        }
        return $this;
    }
    public function exportFiles($offset = 0, $count = 100)
    {
        $files = [];
        if (empty($this->objects)) {
            $this->objects = $this->client->listObjects([
                'Bucket' => $this->getBucket(),
                'MaxKeys' => $count,
            ]);
        } else {
            $this->objects = $this->client->listObjects([
                'Bucket' => $this->getBucket(),
                'MaxKeys' => $count,
                'Marker' => $this->objects[count($this->objects) - 1],
            ]);
        }
        if (empty($this->objects)) {
            return false;
        }
        foreach ($this->objects as $object) {
            if (isset($object['Contents']) && substr($object['Contents'], -1) != '/') {
                $content = $this->client->getObject([
                    'Bucket' => $this->getBucket(),
                    'Key' => $object['Key'],
                ]);
                if (isset($content['Body'])) {
                    $files[] = [
                        'filename' => $object['Key'],
                        'content' => (string)$content['Body'],
                    ];
                }
            }
        }
        return $files;
    }
    public function importFiles(array $files = [])
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        foreach ($files as $file) {
            try {
                // Validate inputs
                if (empty($file['content'])) {
                    throw new \Exception("File content is empty for: " . $file['filename']);
                }

                if (empty($file['filename'])) {
                    throw new \Exception("File name is missing.");
                }

                // Detect MIME type
                $contentType = $finfo->buffer($file['content']);

                // Prepare S3 upload parameters
                $params = [
                    'Body' => $file['content'],
                    'Bucket' => $this->getBucket(),
                    'ContentType' => $contentType,
                    'Key' => ltrim($file['directory'] . '/' . $file['filename'], '/'),
                    'ACL' => 'public-read',
                ];

                // Upload to S3
                $this->client->putObject($params);
                echo "Upload successful for: " . $params['Key'] . PHP_EOL;

            } catch (\Exception $e) {
                error_log("Error uploading " . $file['filename'] . ": " . $e->getMessage());
                $this->errors[] = $e->getMessage();
            }
        }

        return $this;
    }


    public function saveFile($filename)
    {
        $file = $this->mediaHelper->collectFileInfo($this->getMediaBaseDirectory(), $filename);

        $mediaBaseDir = $this->getMediaBaseDirectory();
        $filePath = $mediaBaseDir . '/' . $filename;
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: " . $filePath);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $contentType = $finfo->buffer($file['content']);

        // Force MIME type for CSS files
        if (pathinfo($filename, PATHINFO_EXTENSION) === 'css') {
            $contentType = 'text/css';
        }

        $this->logger->info('wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwto_S3:  -----------' . $filename . '-----------' . $filePath);


        // Upload to S3
        $this->client->putObject($this->getAllParams([
            'Body' => $file['content'],
            'Bucket' => $this->getBucket(),
            'ContentType' => $contentType,
            'Key' => $filename,
        ]));
        return $this;
    }

    public function getAllParams(array $headers = [])
    {
        $headers['ACL'] = 'public-read';
        return $headers;
    }
    public function clear()
    {
        $batch = BatchDelete::fromListObjects($this->client, [
            'Bucket' => $this->getBucket(),
        ]);
        $batch->delete();
        return $this;
    }
    public function exportDirectories($offset = 0, $count = 100)
    {
        return false;
    }
    public function importDirectories(array $dirs = [])
    {
        return $this;
    }
    public function fileExists($filename)
    {
        return $this->client->doesObjectExist($this->getBucket(), $filename);
    }
    public function copyFile($oldFilePath, $newFilePath)
    {
        $this->client->copyObject($this->getAllParams([
            'Bucket' => $this->getBucket(),
            'Key' => $newFilePath,
            'CopySource' => $this->getBucket() . '/' . $oldFilePath,
        ]));

        return $this;
    }
    public function deleteFile($path)
    {
        $this->client->deleteObject([
            'Bucket' => $this->getBucket(),
            'Key' => $path,
        ]);
        return $this;
    }
    public function renameFile($oldFilePath, $newFilePath)
    {
        if(!empty($this->client)) {
            $this->client->copyObject($this->getAllParams([
                'Bucket' => $this->getBucket(),
                'Key' => $newFilePath,
                'CopySource' => $this->getBucket() . '/' . $oldFilePath,
            ]));
            $this->client->deleteObject([
                'Bucket' => $this->getBucket(),
                'Key' => $oldFilePath,
            ]);
        }
        return $this;
    }
    public function getMediaBaseDirectory()
    {
        if ($this->mediaBaseDirectory == null) {
            $this->mediaBaseDirectory = $this->storageHelper->getMediaBaseDir();
        }
        return $this->mediaBaseDirectory;
    }
    protected function getBucket()
    {
        return $this->helper->getBucket();
    }
}
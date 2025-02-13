<?php
namespace Ocode\S3Digital\Model\Theme\Design\Backend;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Theme\Model\Design\Backend\Logo;
use Ocode\S3Digital\Helper\Data;

class S3Logo
{
    protected $helper;
    protected $database;
    public function __construct(
        Data $helper,
        Database $database
    )
    {
        $this->helper = $helper;
        $this->database = $database;
    }

    public function afterBeforeSave(Logo $subject, Logo $result)
    {
        if ($this->helper->checkS3Usage()) {
            $imgFile = $subject::UPLOAD_DIR . '/' . $subject->getValue();
            $relativeImgFile = $this->database->getMediaRelativePath($imgFile);
            $this->database->getStorageDatabaseModel()->saveFile($relativeImgFile);
        }
        return $result;
    }
}
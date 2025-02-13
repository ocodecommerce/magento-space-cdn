<?php
namespace Ocode\S3Digital\Model\Config\Source\Storage\Media;
use Magento\MediaStorage\Model\Config\Source\Storage\Media\Storage;
class S3Storage
{
    public function afterToOptionArray(Storage $subject, $result)
    {
        $result[] = [
            'value' => \Ocode\S3Digital\Model\Core\File\Storage::STORAGE_MEDIA_S3,
            'label' => __('Ocode S3Digital'),
        ];
        return $result;
    }
}
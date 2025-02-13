<?php
namespace Ocode\S3Digital\Block\System\Config\Storage\Media;
class S3Synchronise
{
    public function aroundGetTemplate()
    {
        return 'Ocode_S3Digital::system/config/storage/media/synchronise.phtml';
    }
}
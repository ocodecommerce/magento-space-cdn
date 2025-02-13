<?php

namespace Ocode\S3Digital\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Title extends AbstractDb
{
    protected function _construct()
    {
        $this->_init("downloadable_link_title", "title_id");
    }
}
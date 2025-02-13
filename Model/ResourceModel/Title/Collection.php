<?php

namespace Ocode\S3Digital\Model\ResourceModel\Title;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'title_id';

    public function _construct()
    {
        $this->_init("Ocode\S3Digital\Model\Title", "Ocode\S3Digital\Model\ResourceModel\Title");
    }
}
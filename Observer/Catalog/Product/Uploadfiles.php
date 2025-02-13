<?php

namespace Ocode\S3Digital\Observer\Catalog\Product;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Catalog\Model\Product;
use Ocode\S3Digital\Model\Title;
use Ocode\S3Digital\Helper\Data;
use Magento\Downloadable\Model\Link;
use Magento\Framework\App\Filesystem\DirectoryList;
use Aws\S3\S3Client;
class Uploadfiles implements ObserverInterface
{
    protected $instagramHelper;
    protected $instagramPost;
    protected $helper;
    protected $downloadableLink;
    protected $s3title;
    protected $dirList;

    public function __construct(
        ActionContext $context,
        Link $link,
        DirectoryList $directoryList,
        Title $title,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->downloadableLink=$link;
        $this->s3title = $title;
        $this->dirList = $directoryList;
    }
    public function getClient()
    {
        $accessKey = $this->helper->getAccessKey();
        $secretKey = $this->helper->getSecretKey();
        $bucket = $this->helper->getBucket();
        $region = $this->helper->getRegion();
        $this->bucket = $bucket;
        try {
            $client = new S3Client(array(
                'version'     => 'latest',
                'credentials' => array(
                    'key'    => $accessKey,
                    'secret' => $secretKey,
                ),
                'region'  => $region,
            ));
            $client->listObjects(array('Bucket' => $bucket));
        } catch(\Exception $e) {
            $this->error = $e->getMessage();
            $client = false;
        }
        return $client;
    }
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getId();
        $type = $observer->getProduct()->getTypeId();
        $s3enabled = $this->helper->isS3Enabled();

        if ($type == 'downloadable' && $s3enabled )
        {
            $bucket = $this->helper->getBucket();
            $_myLinksCollection = $this->downloadableLink->getCollection()->addProductToFilter($productId);
            if ($client = $this->getClient()) {
                $this->bucket = $bucket;
                foreach ($_myLinksCollection as $_link) {
                    if ($_link->getLinkType() == "file") {
                        try {
                            $title =  $this->s3title->load($_link->getId())->getTitle();
                            $file = $_link->getLinkFile();
                            $filePath = $this->dirList->getPath('media')."/downloadable/files/links".$file;

                            $file = explode('/', $file);
                            $filename = str_replace(" ", "", $file[count($file)-1]);
                            $filename = "downloadable/files/links/".$productId."/".$filename;
                            $filename = $this->getValidFileName($client, $filename);

                            $result = $client->putObject(
                                array(
                                    'Bucket'       => $this->bucket,
                                    'Key'          => $filename,
                                    'SourceFile'   => $filePath,
                                    'ContentType'  => 'text/plain',
                                    'StorageClass' => 'REDUCED_REDUNDANCY',
                                     'ACL' => 'public-read'
                                )
                            );
                            $data = array(
                                'link_file' => '',
                                'title' => $_link->getTitle(),
                                'link_url' => $result['ObjectURL'],
                                'link_type' => "url",
                            );
                            $_link->addData($data)->setId($_link->getId())->save();
                            $this->s3title->load($_link->getId())
                                ->addData(array("title" => $title))
                                ->setId($_link->getId())
                                ->save();
                        } catch (\Exception $e) {
                            $e->getMessage();
                        }
                    }
                }
            }
        }
    }
    public function getValidFileName($client, $filename, $count = 1)
    {
        $error = true;
        while ($error) {
            if (!$client->doesObjectExist($this->bucket, $filename)) {
                return $filename;
            } else {
                $temp = explode(".", $filename);
                $ext = end($temp);
                array_pop($temp);
                $filename = implode(".", $temp);
                if (strpos($filename, "_") !== false) {
                    $tempFile = explode("_", $filename);
                    if (is_numeric(end($tempFile))) {
                        array_pop($tempFile);
                        $filename = implode("_", $tempFile);
                    }
                }
                $filename .= "_".$count;
                $filename = $filename.".".$ext;
                $count++;
                $filename = $this->getValidFileName($client, $filename, $count);
            }
        }
        return $filename;
    }
}

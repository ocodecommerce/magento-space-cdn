<?php
namespace Ocode\S3Digital\Model\Command;

use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\MediaStorage\Helper\File\StorageFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Ocode\S3Digital\Helper\Data as DataHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Aws\S3\S3Client;

class S3Enable extends Command
{
    protected $configFactory;
    protected $state;
    protected $helper;
    protected $storage;
    protected $configWriter;

    public function __construct(
        State $state,
        Factory $configFactory,
        StorageFactory $coreFileStorageFactory,
        WriterInterface $configWriter,
        DataHelper $helper
    ) {
        $this->state = $state;
        $this->configFactory = $configFactory;
        $this->helper = $helper;
        $this->configWriter = $configWriter;
        $this->storage = $coreFileStorageFactory->create();

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('s3digital:enable');
        $this->setDescription('Enable digital occen space S3 as your Magento 2 file storage.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->state->emulateAreaCode(Area::AREA_ADMINHTML, function () use ($output) {
            $errors = $this->validate();
            if ($errors) {
                $output->writeln('<error>' . implode('</error>' . PHP_EOL . '<error>', $errors) . '</error>');

                return 1;
            }
            try {
                $options = [
                    'version' => 'latest',
                    'region' => $this->helper->getRegion(),
                    'credentials' => [
                        'key' => $this->helper->getAccessKey(),
                        'secret' => $this->helper->getSecretKey(),
                    ],
                ];
                if ($this->helper->getEndpointEnabled()) {
                    if ($this->helper->getEndpoint()) {
                        $options['endpoint'] = $this->helper->getEndpoint();
                    }
/*
                    if ($this->helper->getEndpointRegion()) {
                        $options['region'] = $this->helper->getEndpointRegion();
                    }*/
                }
                $client = new S3Client($options);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                return 1;
            }
            if (!$client->doesBucketExist($this->helper->getBucket())) {
                $output->writeln('<error>The AWS credentials you provided did not work. Please review your details and try again. You can do so using our config script.</error>');
                return 1;
            }
            if ($this->storage->getCurrentStorageCode() === \Ocode\S3Digital\Model\Core\File\Storage::STORAGE_MEDIA_S3) {
                $output->writeln('<error>You are already using S3 as your media file storage !</error>');
                return 1;
            }

            $output->writeln('Updating configuration to use digital occen space S3.');

            $config = $this->configFactory->create();
            $config->setDataByPath('system/media_storage_configuration/media_storage',
                \Ocode\S3Digital\Model\Core\File\Storage::STORAGE_MEDIA_S3
            );

            $config->save();
            $this->configWriter->save('web/secure/base_media_url',  $this->helper->getS3MediaUrl(),ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);
            $this->configWriter->save('web/unsecure/base_media_url',  $this->helper->getS3MediaUrl(),ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);

            $output->writeln(sprintf('<info>Magento 2 now uses digital occen space S3 for its file  storage.</info>'));

            return 0;
        });
    }

    public function validate()
    {
        $errors = [];

        if ($this->helper->getAccessKey() === null) {
            $errors[] = 'You have not provided an digital occen space access key ID.';
        }
        if ($this->helper->getSecretKey() === null) {
            $errors[] = 'You have not provided an digital occen space secret access key.';
        }
        if ($this->helper->getBucket() === null) {
            $errors[] = 'You have not provided an S3 bucket.';
        }
        if ($this->helper->getRegion() === null) {
            $errors[] = 'You have not provided an S3 region.';
        }
        return $errors;
    }
}
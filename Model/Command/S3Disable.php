<?php
namespace Ocode\S3Digital\Model\Command;

use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ocode\S3Digital\Model\Core\File\Storage;


class S3Disable extends \Symfony\Component\Console\Command\Command
{
    protected $configFactory;
    protected $state;
    protected $configWriter;

    public function __construct(
        State $state,
        Factory $configFactory,
        WriterInterface $configWriter
    ) {
        $this->state = $state;
        $this->configFactory = $configFactory;
        $this->configWriter = $configWriter;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('s3digital:disable');
        $this->setDescription('Revert to using the local filesystem as your Magento 2 file storage.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->state->emulateAreaCode(Area::AREA_ADMINHTML, function () use ($output) {
            $output->writeln('Updating configuration to use the local filesystem.');

            $config = $this->configFactory->create();
            $config->setDataByPath(
                'system/media_storage_configuration/media_storage',
                Storage::STORAGE_MEDIA_FILE_SYSTEM
            );
            $config->save();
            $this->configWriter->save('web/secure/base_media_url', "",ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);
            $this->configWriter->save('web/unsecure/base_media_url', "",ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);
            $output->writeln(sprintf('<info>Magento 2 now uses the local filesystem for its file storage.</info>'));

            return 0;
        });
    }
}

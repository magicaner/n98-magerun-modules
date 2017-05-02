<?php
namespace Magic;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\InputArgument;

class ResetOrderShipments extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('magic:reset:order:shipment')
            ->setAliases([
                'reset:order:shipment'
            ])
            ->addArgument('order', InputArgument::REQUIRED, 'Order id')

        ->setDescription('Remove shipment data from order. reset item\'s qty_shipped to 0 ' )
        ;

    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\ConsoleOutputenabled $output
     * @internal param string $package
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if ($this->initMagento()) {

            $order = $input->getArgument('order');

	        $resource = \Mage::getSingleton('core/resource');
	        /** @var \Magento_Db_Adapter_Pdo_Mysql $writeConnection */
            $writeConnection = $resource->getConnection('core_write');

            $writeConnection->query("DELETE FROM {$resource->getTableName('sales/shipment')} WHERE `order_id` = ?", [$order]);
            $writeConnection->query("UPDATE {$resource->getTableName('sales/order_item')} SET `qty_shipped` = 0 WHERE `order_id` = ?", [$order]);
            $writeConnection->query("UPDATE {$resource->getTableName('sales/order')} SET `state` = 'processing', `status` = 'waiting' WHERE `entity_id` = ?", [$order]);


            $output->writeln('<info>done</info>');
        }
    }
}

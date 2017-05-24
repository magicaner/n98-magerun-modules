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

class ResetOrder extends AbstractMagentoCommand
{
    public function isHidden()
    {
        return false;
    }
    protected function configure()
    {
        $this
            ->setName('magic:reset:order')
            ->setAliases([
                'reset:order'
            ])
            ->addArgument('order', InputArgument::REQUIRED, 'Order id')

        ->setDescription('Remove shipment data from order. reset item\'s qty_shipped to 0 . Remove order invoices. Set order status to \'processing\'' )
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
            $writeConnection->query("UPDATE {$resource->getTableName('sales/order')} SET `state` = 'processing', `status` = 'waiting' WHERE `entity_id` = ?",[$order]);

            /*$writeConnection->query("UPDATE {$resource->getTableName('sales/order_item')}
                  SET
                    `qty_shipped` = 0, `qty_invoiced` = 0,
                    `base_tax_invoiced` = 0, `tax_invoiced` = 0,
                    `base_hidden_tax_invoiced` = 0, `hidden_tax_invoiced` = 0,
                    `base_row_invoiced` = 0, `row_invoiced` = 0,
                    `base_discount_invoiced` = 0, `discount_invoiced` = 0
                    WHERE `order_id` = ?", [$order]);

            $writeConnection->query("
                  UPDATE {$resource->getTableName('sales/order')} SET
                    `state` = 'processing', `status` = 'waiting',
                    `base_total_invoiced` = '0', `base_total_paid`='0', `total_invoiced` = '0', `total_paid` = '0',
                    `base_total_invoiced_cost` = '0',
                    `base_tax_invoiced` = '0', `tax_invoiced` = '0',
                    `base_discount_invoiced` = '0', `dicsount_invoiced` = '0',
                    `base_shipping_invoiced` = '0', `shipping_invoiced` = '0'
                    `base_subtotal_invoiced` = '0', `subtotal_invoiced` = '0'
                  WHERE `entity_id` = ?",
                [$order]
            );*/
            $writeConnection->query("UPDATE {$resource->getTableName('sales/order_grid')} SET `status` = 'waiting' WHERE `entity_id` = ?", [$order]);
            //$writeConnection->query("DELETE FROM {$resource->getTableName('sales/invoice')} WHERE `order_id` = ?", [$order]);


            $output->writeln('<info>done</info>');
        }
    }
}

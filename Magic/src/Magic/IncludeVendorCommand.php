<?php
namespace Magic;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Console\Input\StringInput;

class IncludeVendorCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
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
            $function = $input->getOption('f');
            list($model, $method) = explode('::', $function);

            $model = \Mage::getModel($model);
            $params = explode(',', $input->getOption('p'));
            if (!$method) {
                $output->writeln('<error>Method name not found. Try methods below:</error>');


                $class = new \ReflectionClass($model);
                $methods = $class->getMethods();
                foreach ($methods as $m) {
                    $output->writeln('<info>'. $m->name . '</info>');
                }

            } else {
                call_user_func_array(array($model, $method), $params);
                $output->writeln('<info>done</info>');
            }
        }
    }
}

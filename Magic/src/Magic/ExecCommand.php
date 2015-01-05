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

class ExecCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('magic:exec')
            ->setAliases([
                'exec'
            ])
            ->addArgument('method', InputArgument::REQUIRED, 'Model and Method, example: core/observer::cleanCache')
            ->addArgument('parameters', InputArgument::OPTIONAL, 'Method params')

        ->setDescription('Run route')
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

            if (file_exists($this->_magentoRootFolder.'/vendor/autoload.php')) {
                require $this->_magentoRootFolder.'/vendor/autoload.php';
            }

            $function = $input->getArgument('method');
            list($model, $method) = explode('::', $function);

            $model = \Mage::getModel($model);
            if (!$model) {
                $output->writeln('<error>Model name not found. </error>');
                return;
            }

            $params = explode(',', $input->getArgument('parameters'));
            if (!$method) {
                $output->writeln('<error>Method name not found. Try methods below:</error>');


                $class = new \ReflectionClass($model);
                $methods = $class->getMethods();
                foreach ($methods as $m) {
                    $output->writeln('<info>'. $m->name . '</info>');
                }

            } else {
                $res = call_user_func_array(array($model, $method), $params);

                if (!empty($res)) {
                    echo $res;
                }

                $output->writeln('<info>done</info>');
            }
        }
    }
}

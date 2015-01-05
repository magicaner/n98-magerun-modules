<?php

namespace DebugToolbar;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Console\Input\StringInput;

class RemoveCommand extends AbstractMagentoCommand
{
    public $paths = [
	   'skin/frontend/base/default/debug',
	   'app/code/community/Magneto',
	   'app/design/frontend/base/default/layout/debug.xml',
	   'app/design/frontend/base/default/template/debug',
	   'app/design/adminhtml/base/default/layout/debug.xml',
	   'app/design/adminhtml/base/default/template/debug',
	   'app/etc/modules/Magneto_Debug.xml'
    ];

    public $pathsForGitIgnore = [
        'app/etc/modules/Magneto_Debug.xml',
        'app/design/frontend/base/default/template/debug*',
        'app/design/frontend/base/default/layout/debug.xml',
        'app/code/community/Magneto*',
        'skin/frontend/base/default/debug*'
    ];

    protected function configure()
    {
        $this
            ->setName('dev:debug:toolbar:remove')
            ->setAliases([
                'tool:remove','tool:--','tool--'
            ])
            ->addOption('stdout', null, InputOption::VALUE_NONE, 'Remove debug toolbar module')
            ->setDescription('Remove debug toolbar from project')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @internal param string $package
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if ($this->initMagento()) {


            foreach ($this->paths as $path) {
                if (FileSystem::remove($this->_magentoRootFolder . DIRECTORY_SEPARATOR . $path)) {
                    $output->writeln('<info>'.$path.' removed</info>');
                } else {
                    $output->writeln('<error>'.$path.' failed to remove</error>');
                }
            }

            //$this->removeFromGitIgnore();
            $output->writeln('<info>git ignore cleaned up</info>');
            $this->cleanCache($output);

            $output->writeln('<info>done!</info>');

        }
    }

    protected function removeFromGitIgnore()
    {
        $fileName = $this->_magentoRootFolder.'/.gitignore';

        if ($content = FileSystem::readfile($fileName)) {

            $existingPaths = array_map('trim',explode("\n", $content));
            $newPaths = [];

            foreach ($existingPaths as $existingPath) {

                if (!in_array($existingPath, $this->pathsForGitIgnore)) {
                    $newPaths[] = $existingPath;
                }

            }

            FileSystem::filewrite($fileName, implode("\n", $newPaths), 'w', false);
        }
    }

    protected function cleanCache($output)
    {
        $input = new StringInput('cache:clean config');

        // ensure that n98-magerun doesn't stop after first command
        $this->getApplication()->setAutoExit(false);

        // without output
        //$this->getApplication()->run($input, new NullOutput());

        // with output
        $this->getApplication()->run(new StringInput('cache:clean config'), $output);
        $this->getApplication()->run(new StringInput('cache:clean layout'), $output);

        // reactivate auto-exit
        $this->getApplication()->setAutoExit(true);
    }
}

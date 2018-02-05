<?php

namespace UMC;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Console\Input\StringInput;

class DisableCommand extends AbstractMagentoCommand
{
    public $path = 'app/etc/modules/Magneto_Debug.xml';

    protected function configure()
    {
        $this
            ->setName('dev:umc:disable')
            ->setAliases([
                'umc:disable','umc:-','umc-'
            ])
            ->addOption('stdout', null, InputOption::VALUE_NONE, 'Disable Ultimate Module Creator f')
            ->setDescription('Disable Ultimate Module Creator from project.  Module files will stay at project still. 
                You can enable them by using \'mage umc:enable\' command')
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

            $content = file_get_contents($this->_magentoRootFolder . DIRECTORY_SEPARATOR . $this->path);
            $pattern = '/<active>true<\/active>/';
            $content = preg_replace($pattern, '<active>false</active>', $content);
            file_put_contents($this->_magentoRootFolder . DIRECTORY_SEPARATOR . $this->path, $content);

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

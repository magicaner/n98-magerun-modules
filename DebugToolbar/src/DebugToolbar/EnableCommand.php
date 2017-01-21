<?php

namespace DebugToolbar;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Console\Input\StringInput;

class EnableCommand extends AbstractMagentoCommand
{
    public $pathToModule = '/../../data';

    public $pathsForGitIgnore = [
        'app/etc/modules/Magneto_Debug.xml',
        'app/design/frontend/base/default/layout/debug.xml',
        'app/design/frontend/base/default/template/debug*',
        'app/design/adminhtml/base/default/layout/debug.xml',
        'app/design/adminhtml/base/default/template/debug*',
        'app/code/community/Magneto*',
        'skin/frontend/base/default/debug*'
    ];

    protected function configure()
    {
        $this
            ->setName('dev:debug:toolbar:enable')
            ->setAliases([
	           'tool:enable','tool:+','tool+'
            ])
            ->addOption('ignore-cache', null, InputOption::VALUE_NONE, 'Ignore cache')
            ->addOption('gitignore', null, InputOption::VALUE_NONE, 'Add module paths to git ignore')
            //->addOption('stdout', null, InputOption::VALUE_NONE, 'Copy debug toolbar module')

            ->setDescription('Add debug toolbar to project')
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

            $absolutePathToModule = __DIR__ . DIRECTORY_SEPARATOR . $this->pathToModule.'/';
            /* $dir = opendir($absolutePathToModule);
            if ($dir) {
                while (false !== ($file = readdir($dir))) {
                    if (($file != '.') && ($file != '..')) {
                        //var_dump($file);
                        $this->recursiveCopy($absolutePathToModule.'/'.$file, $this->_magentoRootFolder);
                    }
                }
                $output->writeln('<info>Magento_Debug module copied to project</info>');
            } */

            exec('cp -R '.$absolutePathToModule.'/* '.$this->_magentoRootFolder, $result);
            $output->writeln('<info>Magento_Debug module copied to project</info>');

            if (!$input->getOption('ignore-cache')){
                $this->cleanCache($output);
                $output->writeln('<info>clean cache</info>');
            }
            if ($input->getOption('gitignore')) {
                $this->writeToGitIgnore();
                $output->writeln('<info>add paths to git ignore</info>');
            }

            $output->writeln('<info>done!</info>');
        }
    }

    /**
     * @param string $src
     * @param string $dst
     * @param array  $blacklist
     *
     * @return void
     */
    protected function recursiveCopy($src, $dst, $blacklist = array())
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..') && !in_array($file, $blacklist)) {
                if (is_dir($src . '/' . $file)) {
                    $this->recursiveCopy($src . '/' . $file, $dst . '/' . $file, $blacklist);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }


    protected function writeToGitIgnore()
    {
        $fileName = $this->_magentoRootFolder.'/.gitignore';
        if ($content = FileSystem::readfile($fileName)) {

            $existingPaths = array_map('trim', explode("\n", $content));

            foreach ($this->pathsForGitIgnore as $newPath) {
                if (!in_array($newPath, $existingPaths)) {
                    $existingPaths[] = $newPath;
                }
            }
            FileSystem::filewrite($fileName, implode("\n", $existingPaths), 'w', false);

        } else {

            $existingPaths = [];
            foreach ($this->pathsForGitIgnore as $newPath) {
                if (in_array($newPath, $existingPaths)) {
                    $existingPaths[] = $newPath;
                }
            }

            FileSystem::filewrite($fileName, implode("\n", $existingPaths), false);
        }
    }

    protected function cleanCache($output)
    {
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

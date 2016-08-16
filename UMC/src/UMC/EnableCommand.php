<?php

namespace UMC;

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
        'app/etc/modules/Ultimate_ModuleCreator.xml',
        'app/locale/en_US/Ultimate_ModuleCreator.csv',
        'app/design/adminhtml/default/default/layout/ultimate_modulecreator.xml',
        'app/design/adminhtml/default/default/template/ultimate_modulecreator*',
        'app/code/community/Ultimate*',
        'js/ultimate_modulecreator.js',
        'skin/adminhtml/default/default/images/ultimate_modulecreator*',
        'skin/adminhtml/default/default/ultimate_modulecreator.css'
    ];

    protected function configure()
    {
        $this
            ->setName('dev:debug:toolbar:enable')
            ->setAliases([
	           'umc:enable','umc:+','umc+'
            ])
            ->addOption('ignore-cache', null, InputOption::VALUE_NONE, 'Ignore cache')
            ->addOption('gitignore', null, InputOption::VALUE_NONE, 'Add module paths to git ignore')
            //->addOption('stdout', null, InputOption::VALUE_NONE, 'Copy debug toolbar module')

            ->setDescription('Add Ultimate Module Creator to project.')
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
            $output->writeln('<info>Ultima Module Creator module copied to project</info>');

            if (!$input->getOption('ignore-cache')){
                $this->cleanCache($output);
                $output->writeln('<info>alean cache</info>');
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

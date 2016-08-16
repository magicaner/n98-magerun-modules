<?php

namespace UMC;

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
        'app/etc/modules/Ultimate_ModuleCreator.xml',
        'app/locale/en_US/Ultimate_ModuleCreator.csv',
        'app/design/adminhtml/default/default/layout/ultimate_modulecreator.xml',
        'app/design/adminhtml/default/default/template/ultimate_modulecreator',
        'app/code/community/Ultimate',
        'js/ultimate_modulecreator.js',
        'skin/adminhtml/default/default/images/ultimate_modulecreator',
        'skin/adminhtml/default/default/ultimate_modulecreator.css'
    ];

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
            ->setName('dev:debug:toolbar:remove')
            ->setAliases([
                'umc:remove', 'umc:--', 'umc--'
            ])
            ->addOption('stdout', null, InputOption::VALUE_NONE, 'Remove Ultimate Module Creator module')
            ->setDescription('Remove Ultimate Module Creator from project completely');
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
                    $output->writeln('<info>' . $path . ' removed</info>');
                } else {
                    $output->writeln('<error>' . $path . ' failed to remove</error>');
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
        $fileName = $this->_magentoRootFolder . '/.gitignore';

        if ($content = FileSystem::readfile($fileName)) {

            $existingPaths = array_map('trim', explode("\n", $content));
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

<?php
namespace Magic;

use DebugToolbar\FileSystem;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\InputArgument;

class CrudCommand extends AbstractMagentoCommand
{
    public $pathToData = '/../../data/CrudCommand';

    public $templateExtension = '.tpl';
    public $phpExtension = '.php';

    private $_vars = null;
    private $_applicationPaths = null;
    private $_templatePaths = null;
    protected $_templateVersion = 'basic';
    protected $_basePath = '';

    /**
     * @var InputInterface
     */
    protected $_input = null;

    public function isHidden()
    {
        return false;
    }

    public $templates =
        [
            'basic' => [
                'helper_data' => 'Helper/Data',
                'model' => 'Model/{{model}}',
                'model_resource' => 'Model/Resource/{{model}}',
                'model_resource_collection' => 'Model/Resource/{{model}}/Collection',
                'admin_controller' => 'controllers/Adminhtml/{{controller}}',
                'block_admin_model' => 'Block/Adminhtml/{{model}}',
                'block_admin_model_grid' => 'Block/Adminhtml/{{model}}/Grid',
                'block_admin_model_edit' => 'Block/Adminhtml/{{model}}/Edit',
                'block_admin_model_edit_form' => 'Block/Adminhtml/{{model}}/Edit/Form'
            ],
            'advanced' => [
                'helper_data' => 'Helper/Data',
                'model' => 'Model/{{model}}',
                'model_resource' => 'Model/Resource/{{model}}',
                'model_resource_collection' => 'Model/Resource/{{model}}/Collection',
                'admin_controller' => 'controllers/Adminhtml/{{controller}}',
                'block_admin_model' => 'Block/Adminhtml/{{model}}',
                'block_admin_model_grid' => 'Block/Adminhtml/{{model}}/Grid',
                'block_admin_model_edit' => 'Block/Adminhtml/{{model}}/Edit',
                'block_admin_model_edit_form' => 'Block/Adminhtml/{{model}}/Edit/Form',
                'block_admin_model_edit_form_tabs' => 'Block/Adminhtml/{{model}}/Edit/Form/Tabs',
                'block_admin_model_edit_form_tabs_general' => 'Block/Adminhtml/{{model}}/Edit/Form/Tabs/General'
            ]
        ];


    protected function configure()
    {
        $this
            ->setName('magic:crud')
            ->setAliases([
                'crud'
            ])
            ->addArgument('module', InputArgument::REQUIRED, 'Module name')
            ->addArgument('table', InputArgument::REQUIRED, 'Table name')
            ->addArgument('model', InputArgument::REQUIRED, 'Model name')

            ->addOption('menu', 'menu', InputOption::VALUE_OPTIONAL, 'Selected admin menu')
            ->addOption('force', 'force', InputOption::VALUE_OPTIONAL, 'Rewrite existing files', false)
            ->addOption('basepath', 'basepath', InputOption::VALUE_OPTIONAL, 'Base path', false)
            ->addOption('template', 'template', InputOption::VALUE_OPTIONAL,
                'Template version. \advanced\' or \'basic\'. By default \'basic\' is used', false)

        ->setDescription('Create CRUD for given table and model name')
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
            $this->initVariables($input);
            $this->generateTemplates();
        }
    }

    protected function getDataPath()
    {
        return $absolutePathToModule = __DIR__ . DIRECTORY_SEPARATOR . $this->pathToData;
    }

    protected function getTemplatesPath()
    {
        return $this->getDataPath() . '/templates/' . $this->_templateVersion;
    }

    protected function getTemplatePath($name)
    {
        if (isset($this->templates[$name])) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $this->templates[$name]);
            return $this->getTemplatesPath() . DIRECTORY_SEPARATOR . $path . $this->templateExtension;
        } else {
            return false;
        }
    }

    protected function getTemplatePaths()
    {
        if (isset($this->_templatePaths)) {
            return $this->_templatePaths;
        }

        $paths = $this->templates[$this->_templateVersion];

        foreach ($paths as &$path) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            $path = $this->getTemplatesPath() . DIRECTORY_SEPARATOR . $path . $this->templateExtension;

            $path = str_replace(['{{model}}', '{{controller}}'], ['Model', 'Controller'], $path);
        }

        return $this->_templatePaths = $paths;
    }

    protected function getApplicationPaths()
    {
        if (isset($this->_applicationPaths)) {
            return $this->_applicationPaths;
        }
        $paths = $this->templates[$this->_templateVersion];

        $moduleName = $this->uc_words($this->_vars['module']);
        $codePool = (string)\Mage::app()->getConfig()->getModuleConfig($moduleName)->codePool;

        if (!$codePool) {
            throw new \Exception('Code pull not found for mdoule '. $moduleName);
        }

        $moduleDir = \Mage::app()->getConfig()->getOptions()->getCodeDir()
                    . DS . $codePool . DS . $this->uc_words($moduleName,'/','_');

        foreach ($paths as &$path) {

            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            $path = $moduleDir . DS . $path . $this->phpExtension;

            $path = str_replace(
                ['{{model}}', '{{controller}}'],
                [($this->_basePath ? $this->_basePath . '/' : '' ) . $this->uc_words($this->_vars['model'], '/'), $this->uc_words($this->_vars['model'], '/').'Controller'],
                $path
            );
        }

        return $this->_applicationPaths = $paths;

    }

    protected function generateTemplates()
    {
        $templates = $this->getTemplatePaths();
        foreach ($templates as $name => $path) {

            $content = $this->compileTemplate($path);
            $this->saveTemplate($name, $content);
        }
    }

    protected function compileTemplate($path)
    {
        $content = file_get_contents($path);
        $pattern = '/\{\{.*?\}\}/mis';

        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[0] as $variable) {
                $value = $this->compileVariable($variable);
                $content = str_replace($variable, $value, $content);
            }
        }
        return $content;
    }

    protected function saveTemplate($name, $content)
    {
        $paths = $this->getApplicationPaths();
        $file = new FileSystem();
        if (isset($paths[$name])) {
            $fileExists = file_exists($paths[$name]);
            if (!$fileExists) {
                $file->filewrite($paths[$name], $content);
            } elseif ($this->getInput()->getOption('force')) {
                $file->filewrite($paths[$name], $content);
            }
        } else {
            throw new \Exception('Path for \'' . $name . '\' not found');
        }
    }

    protected function initVariables(InputInterface $input)
    {
        $this->_input = $input;
        if ($this->_input->getOption('template')) {
            $this->_templateVersion = $this->_input->getOption('template');
        }

        if ($this->_input->getOption('template')) {
            $this->_basePath = $this->_input->getOption('basepath');
        }

        $module = $this->_input->getArgument('module');
        $model = $this->_input->getArgument('model');
        $table = $this->_input->getArgument('table');

        @list($module, $moduleAlias) = @explode(':', $module);
        if (!$moduleAlias) {
            $moduleAlias = strtolower($module);
        }

        @list($tableAlias, $table) = @explode(':', $table);
        if (!$table) {
            $path = sprintf(
                'global/models/%s_resource/entities/%s/table',
                $moduleAlias, $tableAlias
            );

            $table = (string)\Mage::getConfig()->getNode($path);
        }


        $this->_vars = [
            'module' => $module,
            'module_alias' => $moduleAlias,
            'model' => strtolower($model),
            'table' => $table,
            'table_alias' => $tableAlias,
            'primarykey' => $this->_getTablePrimaryKey($table),
            'menu' => $this->_input->getOption('menu'),

            'model_class_name' => $this->_generateModelClassName($module, $model),
            'model_resource_class_name' => $this->_generateModelResourceClassName($module, $model),
            'model_resource_collection_class_name' => $this->_generateModelResourceCollectionClassName($module, $model),
            'admin_controller_class_name' => $this->_generateAdminControllerClassName($module, $model),
            'helper_data_class_name' => $this->_generatHelperClassName($module, 'data'),

            'admin_controller' => 'adminhtml_' . $model,

            'block_admin_grid_container' => $this->_generateBlockName($model, 'block_admin_grid_container'),
            'block_admin_grid' => $this->_generateBlockName($model, 'block_admin_grid'),
            'block_admin_edit' => $this->_generateBlockName($model, 'block_admin_edit'),
            'block_admin_edit_form' => $this->_generateBlockName($model, 'block_admin_edit_form'),

            'block_admin_grid_class_name' => $this->_generateBlockClassName($module, $model, 'block_admin_grid'),
            'block_admin_edit_class_name' => $this->_generateBlockClassName($module, $model, 'block_admin_edit'),
            'block_admin_edit_form_class_name' => $this->_generateBlockClassName($module, $model, 'block_admin_edit_form'),
            'block_admin_grid_container_class_name' => $this->_generateBlockClassName($module, $model, 'block_admin_grid_container'),
        ];

    }

    protected function getVariableValue($variable)
    {
        if (isset($this->_vars[$variable])) {
            return $this->_vars[$variable];
        } else {
            return false;
        }
    }

    protected function compileVariable($origVariable)
    {
        $variable = trim($origVariable,'{{}}');

        $modificators = explode('|',$variable);
        $variable = array_shift($modificators); // remove first element of array
        if (false == ($value = $this->getVariableValue($variable))) {
            return $origVariable;
        }

        foreach ($modificators as $modificator) {

            list($modificator, $params)  = @explode(':', $modificator);
            if ($params) {
                $params = array_merge([$value], explode(';', $params));
            }


            if (method_exists($this, '_modificator' . $modificator)) {
                if ($params) {
                    $value = call_user_func_array([$this, '_modificator' . $modificator], $params);
                } else {
                    $value = call_user_func([$this, '_modificator' . $modificator], $value);
                }

            } elseif (method_exists($this, $modificator)) {
                if ($params) {
                    $value = call_user_func_array([$this, $modificator], $params);
                } else {
                    $value = call_user_func([$this, $modificator], $value);
                }
            } elseif (function_exists($modificator)) {
                if ($params) {
                    $value = call_user_func_array($modificator, explode(';', $params));
                } else {
                    $value = call_user_func($modificator, $value);
                }
            }
        }

        return $value;
    }

    private function _generateModelClassName($module, $model)
    {
        return $this->uc_words($module).'_Model_'.$this->uc_words($model);
    }

    private function _generateModelResourceClassName($module, $model)
    {
        return $this->uc_words($module).'_Model_Resource_'.$this->uc_words($model);
    }

    private function _generateModelResourceCollectionClassName($module, $model)
    {
        return $this->uc_words($module).'_Model_Resource_'.$this->uc_words($model).'_Collection';
    }

    private function _generateAdminControllerClassName($module, $model)
    {
        return $this->uc_words($module).'_Adminhtml_'.$this->uc_words($model).'Controller';
    }

    private function _getTablePrimaryKey($tableName)
    {
        $resource = \Mage::getSingleton('core/resource');

        $connection = $resource->getConnection('core_read');

        $indexes = $connection->getIndexList($tableName);
        if (isset($indexes['PRIMARY']) && isset($indexes['PRIMARY']['fields']) ) {
            return $indexes['PRIMARY']['fields'][0];
        } else {
            return 'entity_id';
        }

        return $connection->getPrimaryKeyName($tableName);
    }

    private function _generateBlockName($model, $blockName)
    {
        $patterns = [
            'block_admin_grid_container' => 'adminhtml_{{model}}',
            'block_admin_grid' => 'adminhtml_{{model}}_grid',
            'block_admin_edit' => 'adminhtml_{{model}}_edit',
            'block_admin_edit_form' => 'adminhtml_{{model}}_edit_form'
        ];

        if (isset($patterns[$blockName])) {
            return str_replace('{{model}}', $model, $patterns[$blockName]);
        } else {
            return $blockName;
        }
    }

    private function _generateBlockClassName($module, $model, $blockName)
    {
        $patterns = [
            'block_admin_grid_container' => '{{module}}_block_adminhtml_{{model}}',
            'block_admin_grid' => '{{module}}_block_adminhtml_{{model}}_grid',
            'block_admin_edit' => '{{module}}_block_adminhtml_{{model}}_edit',
            'block_admin_edit_form' => '{{module}}_block_adminhtml_{{model}}_edit_form',
        ];

        if (isset($patterns[$blockName])) {
            return $this->uc_words(str_replace(['{{module}}','{{model}}'], [$module, $model], $patterns[$blockName]));
        } else {
            return $blockName;
        }
    }

    private function _generatHelperClassName($module, $name)
    {
        return $this->uc_words($module . '_Helper_' . $name);
    }

    /**
     * Tiny function to enhance functionality of ucwords
     *
     * Will capitalize first letters and convert separators if needed
     *
     * @param string $str
     * @param string $destSep
     * @param string $srcSep
     * @return string
     */
    public function uc_words($str, $destSep='_', $srcSep='_')
    {
        return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
    }

    /**
     * input
     *
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->_input;
    }

    public function _modificatorUcWords($str, $destSep='_', $srcSep='_')
    {
        return $this->uc_words($str, $destSep, $srcSep);
    }
}

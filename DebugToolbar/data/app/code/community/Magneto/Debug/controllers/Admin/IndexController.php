<?php
/**
 * eCommeleon Ltd ecommerce software
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category Magneto
 * @package Magneto_Debug
 * @copyright Copyright © 2014 eCommeleon Ltd (http://ecommeleon.com/)
 * @author Misha Medgitov <medgitov@gmail.com>
 */
/**
 * index controller
 *
 * @category Magneto
 * @package Magneto_Debug
 * @author Misha Medgitov <medgitov@gmail.com>
 */
class Magneto_Debug_Admin_IndexController extends Mage_Adminhtml_Controller_Action
{
    public $directory_separator = '/';

    public $root_path = null;

    public function _construct()
    {
        parent::_construct();
        $this->root_path = Mage::helper('debug')->getRootPath();
        $this->directory_separator = Mage::helper('debug')->getDirectorySeparator();

    }

    /**
     * Controller predispatch method
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function preDispatch()
    {
        // override admin store design settings via stores section
        Mage::getDesign()
            ->setArea($this->_currentArea)
            ->setPackageName((string)Mage::getConfig()->getNode('stores/admin/design/package/name'))
            ->setTheme((string)Mage::getConfig()->getNode('stores/admin/design/theme/default'))
        ;

        Mage::app()->setCurrentStore(Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID));

        foreach (array('layout', 'template', 'skin', 'locale') as $type) {
            if ($value = (string)Mage::getConfig()->getNode("stores/admin/design/theme/{$type}")) {
                Mage::getDesign()->setTheme($type, $value);
            }
        }

        $this->getLayout()->setArea($this->_currentArea);

        return $this;
    }
    /**
     * generate debug panel
     *
     * @param string $title   block title
     * @param string $content block content
     * @return string
     */
    private function _debugPanel($title, $content)
    {
        $block = new Mage_Core_Block_Template();
        $block->setTemplate('debug/simplepanel.phtml');
        $block->assign('title', $title);
        $block->assign('content', $content);
        return $block->toHtml();
    }

    /**
     * print template details
     *
     * @return void
     */
    public function viewTemplateAction()
    {
        $fileName = $this->getRequest()->get('template');
        $absoluteFilepath = realpath(Mage::getBaseDir('design') . DIRECTORY_SEPARATOR . $fileName);
        $source = highlight_string(file_get_contents($absoluteFilepath), true) ;

        $this->getResponse()->setBody($this->_debugPanel(
            "Template Source: <code class=\"autoselect\">$fileName</code><br/>\n
            Absolute Path: <code class=\"autoselect\">".Mage::helper('debug')->fixAbsolutePath($absoluteFilepath)."</code>",
            ''.$source.''
            )
        );
    }

    /**
     * print block details
     *
     * @return void
     */
    public function viewBlockAction()
    {
        $blockClass = $this->getRequest()->get('block');
        $absoluteFilepath = Mage::helper('debug')->getBlockFilename($blockClass);

        if ($absoluteFilepath) {
            $source = highlight_string(file_get_contents($absoluteFilepath), true) ;
        } else {
            $source = 'Source file not found';
        }
        $this->getResponse()->setBody($this->_debugPanel("Block Source: <code class=\"autoselect\">".$blockClass."</code><br/>\n
            Absolute path: <code class=\"autoselect\">".Mage::helper('debug')->fixAbsolutePath($absoluteFilepath).'</code>', ''.$source.''));
    }

    /**
     * print sql details
     *
     * @return void
     */
    public function viewSqlSelectAction()
    {
        $con = Mage::getSingleton('core/resource')->getConnection('core_write');
        $query = $this->getRequest()->getParam('sql');
        $queryParams = $this->getRequest()->getParam('params');

        $result = $con->query($query, $queryParams);

        $items = array();
        $headers = array();
        while ( $row = $result->fetch(PDO::FETCH_ASSOC) ) {
            $items[] = $row;

            if ( empty($headers) ) {
                $headers = array_keys($row);
            }
        }

        $block = new Mage_Core_Block_Template();
        $block->setTemplate('debug/arrayformat.phtml');
        $block->assign('title', 'SQL Select');
        $block->assign('headers', $headers);
        $block->assign('items', $items);
        $block->assign('query', $query);
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * print files details
     *
     * @return void
     */
    public function viewFilesWithHandleAction()
    {
        $layoutHandle = $this->getRequest()->getParam('layout');
        $title = "Files with layout updates for handle {$layoutHandle}";
        if ( !$layoutHandle ) {

        }

        $updateFiles = Mage::helper('debug')->getLayoutUpdatesFiles();
        /* @var $design Mage_Core_Model_Design_Package */
        $design = Mage::getSingleton('core/design_package');

        // search handle in the files
        $handleFiles = array();
        foreach ($updateFiles as $file) {
            $filename = $design->getLayoutFilename($file, array(
                '_area'    => $design->getArea(),
                '_package' => $design->getPackageName(),
                '_theme'   => $design->getTheme('layout')
            ));
            if (!is_readable($filename)) {
                continue;
            }
            $fileStr = file_get_contents($filename);

            $fileXml = simplexml_load_string($fileStr, Mage::getConfig()->getModelClassName('core/layout_element'));
            if (!$fileXml instanceof SimpleXMLElement) {
                continue;
            }

            $result = $fileXml->xpath("/layout/" . $layoutHandle);
            if ($result) {
                $handleFiles[$filename] = $result;
            }
        }

        // TODO: search handle in db layout updates

        $block = new Mage_Core_Block_Template();
        $block->setTemplate('debug/handledetails.phtml');
        $block->assign('title', $title);
        $block->assign('handleFiles', $handleFiles);
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * print query explaination
     *
     * @return void
     */
    public function viewSqlExplainAction()
    {
        $con = Mage::getSingleton('core/resource')->getConnection('core_write');
        $query = $this->getRequest()->getParam('sql');
        $queryParams = $this->getRequest()->getParam('params');

        $result = $con->query("EXPLAIN {$query}", $queryParams);

        $items = array();
        $headers = array();
        while ( $row = $result->fetch(PDO::FETCH_ASSOC) ) {
            $items[] = $row;

            if ( empty($headers) ) {
                $headers = array_keys($row);
            }
        }

        $block = new Mage_Core_Block_Template(); //Is this the correct way?
        $block->setTemplate('debug/arrayformat.phtml');
        $block->assign('title', 'SQL Explain');
        $block->assign('headers', $headers);
        $block->assign('items', $items);
        $block->assign('query', $query);
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * clear cahcne and refresh page
     *
     * @return void
     */
    public function clearCacheAction()
    {
        $content = Mage::helper('debug')->cleanCache();
        Mage::getSingleton('core/session')->addSuccess("Magento's caches were cleared.");
        $this->_redirectReferer();
    }

    /**
     * toggle translate inline action
     *
     * @return void
     */
    public function toggleTranslateInlineAction()
    {
        $currentStatus = Mage::getStoreConfig('dev/translate_inline/active');
        $newStatus = !$currentStatus;

        $config = Mage::app()->getConfig();
        $config->saveConfig('dev/translate_inline/active', $newStatus);
        $config->saveConfig('dev/translate_inline/active_admin', $newStatus);

        // Toggle translate cache too
        $allTypes = Mage::app()->useCache();
        $allTypes['translate'] = !$newStatus; // Cache off when translate is on
        Mage::app()->saveUseCache($allTypes);

        // clear cache
        Mage::app()->getCacheInstance()->flush();

        Mage::getSingleton('core/session')->addSuccess('Translate inline set to ' . var_export($newStatus, true));
        $this->_redirectReferer();
    }

    /**
     * toggle template hints action
     *
     * @return void
     */
    public function toggleTemplateHintsAction()
    {
        $currentStatus = Mage::getStoreConfig('dev/debug/template_hints');
        $newStatus = !$currentStatus;

        $config = Mage::getModel('core/config');
        $config->saveConfig(
            'dev/debug/template_hints',
            $newStatus,
            'websites',
            Mage::app()->getStore()->getWebsiteId()
        );
        $config->saveConfig(
            'dev/debug/template_hints_blocks',
            $newStatus,
            'websites',
            Mage::app()->getStore()->getWebsiteId()
        );

        Mage::app()->cleanCache();

        Mage::getSingleton('core/session')->addSuccess('Template hints set to ' . var_export($newStatus, true));
        $this->_redirectReferer();
    }

    /**
     * toggle module status action
     *
     * @return void
     */
    public function toggleModuleStatusAction()
    {
        $title = "Toggle Module Status";
        $moduleName = $this->getRequest()->getParam('module');
        if ( !$moduleName ) {
            $this->getResponse()->setBody($this->_debugPanel($title, "Invalid module name supplied. "));
            return;
        }
        $config = Mage::getConfig();

        $moduleConfig = Mage::getConfig()->getModuleConfig($moduleName);
        if ( !$moduleConfig  ) {
            $this->getResponse()->setBody($this->_debugPanel($title, "Unable to load supplied module. "));
            return;
        }


        $moduleCurrentStatus = $moduleConfig->is('active');
        $moduleNewStatus = !$moduleCurrentStatus;
        $moduleConfigFile = $config->getOptions()->getEtcDir() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName . '.xml';
        $configContent = file_get_contents($moduleConfigFile);


        $contents = "<br/>Active status switched to " . ($moduleNewStatus ? true : false)
                  . " for module {$moduleName} in file {$moduleConfigFile}:";

        $contents .= "<br/><code>" . htmlspecialchars($configContent) . "</code>";

        $configContent = str_replace(
            "<active>" . ($moduleCurrentStatus ? true : false) ."</active>",
            "<active>" . ($moduleNewStatus ? true : false) . "</active>",
            $configContent
        );

        if ( file_put_contents($moduleConfigFile, $configContent) === FALSE ) {
            $this->getResponse()
                ->setBody($this->_debugPanel(
                     $title,
                     "Failed to write configuration. (Web Server's permissions for {$moduleConfigFile}?!)"
                    )
                );
            return $this;
        }

        Mage::helper('debug')->cleanCache();

        $contents .= "<br/><code>" . htmlspecialchars($configContent) . "</code>";
        $contents .= "<br/><br/><i>WARNING: This feature doesn't support usage of multiple frontends.</i>";

        $this->getResponse()->setBody($this->_debugPanel($title, $contents));
    }


    /**
     * read config as xml
     *
     * @return void
     */
    public function downloadConfigAction()
    {
        header("Content-type: text/xml");
        $this->getResponse()->setBody(Mage::app()->getConfig()->getNode()->asXML());
    }

    /**
     * read config as text
     *
     * @return void
     */
    public function downloadConfigAsTextAction()
    {
        header("Content-type: text/plain");
        $configs = Mage::app()->getConfig()->getNode();
        $items = array();
        Magneto_Debug_Block_Config::xml2array($configs, $items);
        $output = '';
        foreach ($items as $key => $value) {
            $output .= "$key = $value\n";
        }
        $this->getResponse()->setBody($output);
    }

    /**
     * show sql profiler
     *
     * @return void
     */
    public function showSqlProfilerAction()
    {
        $config = Mage::getConfig()->getNode('global/resources/default_setup/connection/profiler');
        Mage::getSingleton('core/resource')->getConnection('core_write')->getProfiler()->setEnabled(false);
        var_dump($config);
    }

    /**
     * FIXME: This needs to be corrected
     * toggle sql profiler action
     *
     * @return void
     */
    public function toggleSqlProfilerAction()
    {
        $localConfigFile = Mage::getBaseDir('etc').DIRECTORY_SEPARATOR.'local.xml';
        $localConfigBackupFile = Mage::getBaseDir('etc').DIRECTORY_SEPARATOR.'local-magneto.xml';

        $configContent = file_get_contents($localConfigFile);
        $xml = new SimpleXMLElement($configContent);
        $profiler = $xml->global->resources->default_setup->connection->profiler;
        if ( (int)$xml->global->resources->default_setup->connection->profiler != 1 ) {
            $xml->global->resources->default_setup->connection->addChild('profiler', 1);
        } else {
            unset($xml->global->resources->default_setup->connection->profiler);
        }

        // backup config file
        if ( file_put_contents($localConfigBackupFile, $configContent) === FALSE ) {
            Mage::getSingleton('core/session')->addError("Operation aborted: couldn't create backup for config file");
            $this->_redirectReferer();
        }

        if ( $xml->saveXML($localConfigFile) === FALSE ) {
            Mage::getSingleton('core/session')->addError("Couldn't save {$localConfigFile}: check write permissions.");
            $this->_redirectReferer();
        }
        Mage::getSingleton('core/session')->addSuccess("SQL profiler status changed in local.xml");

        Mage::helper('debug')->cleanCache();
        $this->_redirectReferer();
    }

    /**
     * index
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * search grouped class action
     *
     * @return void
     */
    public function searchGroupedClassAction()
    {
        if ( $this->getRequest()->isPost() ) {
            $uri = $this->getRequest()->getPost('uri');
            $groupType = $this->getRequest()->getPost('group');
            $items = array();

            $groupTypes = array('model', 'block', 'helper');

            if (!empty($uri)) {
                if ( $groupType == 'all' ) {
                    foreach ($groupTypes as $type) {
                        switch ($type) {
                            case 'helper':
                                if (strpos($uri, '/') === false) {
                                    $items[$type] = Mage::getConfig()->getGroupedClassName($type, $uri.'/data');
                                } else {
                                    $items[$type] = Mage::getConfig()->getGroupedClassName($type, $uri);
                                }

                                break;
                            default:
                                $items[$type] = Mage::getConfig()->getGroupedClassName($type, $uri);
                                break;
                        }

                    }
                } else {
                    $items[$groupType] = Mage::getConfig()->getGroupedClassName($groupType, $uri);
                }

                $block = new Mage_Core_Block_Template();
                $block->setTemplate('debug/groupedclasssearch.phtml');
                $block->assign('items', $items);
                $this->getResponse()->setBody($block->toHtml());
            } else {
                $this->getResponse()->setBody("Please fill in a search query");
            }
        }
    }

    /**
     * search config
     *
     * @return void
     */
    public function searchConfigAction()
    {
        if ( $this->getRequest()->isPost() ) {
            $result['error'] = 0;

            $query = $this->getRequest()->getPost('query');
            if ( !empty($query) ) {
                $configs = Mage::app()->getConfig()->getNode($query);
                $items = array();
                Magneto_Debug_Block_Config::xml2array($configs, $items, $query);

                $block = new Mage_Core_Block_Template(); //Is this the correct way?
                $block->setTemplate('debug/configsearch.phtml');
                $block->assign('items', $items);
                $this->getResponse()->setBody($block->toHtml());
            } else {
                $result['error'] = 1;
                $result['message'] = 'Search query cannot be empty.';
            }
        }
    }
}

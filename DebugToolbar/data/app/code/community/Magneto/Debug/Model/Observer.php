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
 * events observer
 *
 * @category Magneto
 * @package Magneto_Debug
 * @author Misha Medgitov <medgitov@gmail.com>
 */
class Magneto_Debug_Model_Observer
{
    static $id = 1;
    /**
     * @var array
     */
    private $_actions = array();

    /**
     * List of assoc array with class, type and sql keys
     *
     * @var array
     */
    private $collections = array();

    // private $layoutUpdates = array();

    /**
     * @var array
     */
    private $models = array();

    /**
     * @var array
     */
    private $blocks = array();

    /**
     * @var array
     */
    private $layoutBlocks = array();

    /**
     * @var boolean
     */
    private $isModuleActive = false;

    /**
     * check if module active and save ts value
     * @return void
     */
    public function __construct()
    {
        $this->isModuleActive = $this->isModuleEnabled();
    }


    public function isModuleEnabled()
    {
        if (Mage::getIsDeveloperMode() == false ) {
            return false;
        }

        $isDebugEnable = (int)Mage::getConfig()->getNode('default/debug/options/enable');
        $clientIp = Mage::app()->getRequest()->getClientIp();
        $allow = false;

        if( $isDebugEnable ){
            $allow = true;
            // Code copy-pasted from core/helper, isDevAllowed method
            // I cannot use that method because the client ip is not always correct (e.g varnish)
            /* Mage_Core_Model_Config_Element */
            $allowedIps = (string)Mage::getConfig()->getNode('default/dev/restrict/allow_ips');
            if ( $isDebugEnable && !empty($allowedIps) && !empty($clientIp)) {
                $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
                if (array_search($clientIp, $allowedIps) === false
                    && array_search(Mage::helper('core/http')->getHttpHost(), $allowedIps) === false) {
                        $allow = false;
                    }
            }
        }

        return $allow;
    }

    /**
     * get models
     *
     * @return array
     */
    public function getModels()
    {
        return $this->models;
    }

    /**
     * get blocks
     *
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocks;
    }
    /**
     * get layout blocks
     *
     * @return array
     */
    public function getLayoutBlocks()
    {
        return $this->layoutBlocks;
    }

    /**
     * get collections
     *
     * @return array
     */
    public function getCollections()
    {
        return $this->collections;
    }
    // public function getLayoutUpdates() { return $this->layoutUpdates; }

    /**
     * get queries
     *
     * @return array
     */
    public function getQueries()
    {
        //TODO: implement profiler for connections other than 'core_write'
        $profiler = Mage::getSingleton('core/resource')->getConnection('core_write')->getProfiler();
        $queries = array();

        if ($profiler) {
            $queries = $profiler->getQueryProfiles();
        }

        return $queries;
    }


    /**
     * skip core bloks
     *
     * @return boolean
     */
    public function skipCoreBlocks()
    {
        return false;
    }

    /**
     * on layout generate
     *
     * @param Varien_Event_Observer $observer observer
     * @return void
     */
    public function onLayoutGenerate(Varien_Event_Observer $observer)
    {
        if (!$this->isModuleActive) {
            return;
        }
        $layout = $observer->getEvent()->getLayout();
        $layoutBlocks = $layout->getAllBlocks();

        // After layout generates all the blocks
        foreach ($layoutBlocks as $block) {
            $blockStruct = array();
            $blockStruct['class'] = get_class($block);
            $blockStruct['layout_name'] = $block->getNameInLayout();
            if ( method_exists($block, 'getTemplateFile') ) {
                $blockStruct['template'] = $block->getTemplateFile();
            } else {
                $blockStruct['template'] = '';
            }
            if ( method_exists($block, 'getViewVars') ) {
                $blockStruct['context'] = $block->getViewVars();
            } else {
                $blockStruct['context'] = NULL;
            }
            $this->layoutBlocks[] = $blockStruct;
        }
    }

    /**
     * on block generate
     *
     * @param Varien_Event_Observer $observer observer
     * @return Magneto_Debug_Model_Observer observer
     */
    public function onBlockToHtml(Varien_Event_Observer $observer)
    {
        if (!$this->isModuleActive) {
            return;
        }

        /** @var Varien_Event */
        $event = $observer->getEvent();
        $block = $event->getBlock();
        $template = $block->getTemplateFile();
        $viewVars = $block->getViewVars();

        if ( $this->skipCoreBlocks() && strpos(get_class($block), 'Mage_') === 0 ) {
            return $this;
        }

        // Don't list blocks from Debug module
        // if( strpos(get_class($block), 'Magneto_Debug_Block')===0 )
            // return $this;

        $blockStruct = array();
        $blockStruct['class'] = get_class($block);
        $blockStruct['layout_name'] = $block->getNameInLayout();
        if ( method_exists($block, 'getTemplateFile') ) {
            $blockStruct['template'] = $block->getTemplateFile();
        } else {
            $blockStruct['template'] = '';
        }
        if ( method_exists($block, 'getViewVars') ) {
            $blockStruct['context'] = $block->getViewVars();
        } else {
            $blockStruct['context'] = NULL;
        }

        $this->blocks[] = $blockStruct;

        return $this;
    }

    /**
     * on action post dispatch
     *
     * @param Varien_Event_Observer $event observer
     * @return void
     */
    public function onActionPostDispatch(Varien_Event_Observer $event)
    {
        if (!$this->isModuleActive) {
            return;
        }

        $action = $event->getControllerAction();
        $response = $event->getResponse();

        $actionStruct = array();
        $actionStruct['class'] = get_class($action);
        $actionStruct['action_name'] = $action->getFullActionName();
        $actionStruct['route_name'] = $action->getRequest()->getRouteName();

        $this->_actions[] = $actionStruct;
    }


    // controller_action_layout_generate_blocks_after
    /**
     * on collection load
     *
     * @param Varien_Event_Observer $event observer
     * @return void
     */
    public function onCollectionLoad(Varien_Event_Observer $event)
    {
        if (!$this->isModuleActive) {
            return;
        }

        /** @var Mage_Core_Model_Mysql4_Store_Collection */
        $collection = $event->getCollection();

        $collectionStruct = array();
        $collectionStruct['sql'] = $collection->getSelectSql(true);
        $collectionStruct['type'] = 'mysql';
        $collectionStruct['class'] = get_class($collection);
        $this->collections[] = $collectionStruct;
    }

    /**
     * add js onhover for each html template block
     *
     * @param Varien_Event_Observer $event observer
     * @return void
     */
    public function onAfterToHtml(Varien_Event_Observer $event)
    {
        if (Mage::app()->getRequest()->isAjax()) {
            return;
        }

        /* @var $block Mage_Core_Block_Template */

        $blockTags = ['html'];

        $transport = $event->getTransport();
        $html = $transport->getHtml();
        $block = $event->getBlock();

        if ($block instanceof Magneto_Debug_Block_Abstract) {
            return;
        }

        if ($block instanceof Mage_Core_Block_Profiler) {
            return;
        }

        if (preg_match('/scripts/', $block->getNameInLayout())) {
            return;
        }




        $absoluteFilepath = Mage::getBaseDir('design') . DIRECTORY_SEPARATOR .  $block->getTemplateFile();

        if (!file_exists($absoluteFilepath) || !$block->getTemplateFile()) {
            return;
        }

        $absoluteFilepath = Mage::helper('debug')->fixAbsolutePath($absoluteFilepath);

        $data = [
            'class' => get_class($block),
            'alias' => $block->getAlias(),
            'name' => $block->getNameInLayout(),
            'template' => $absoluteFilepath,
            //'layout' => $block->get
            //'data' => $block->getData()
        ];

        $blockJson = Zend_Json::encode($data);

        if ($block instanceof Mage_Page_Block_Html) {
            $pattern = '/^(.*?)<([^!<>\s]+)(\s*.*?>.*)$/imsu';
            $html = preg_replace_callback(
                $pattern,
                function($matches) use ($blockTags, $blockJson) {
                    $tag = trim($matches[2]);

                    if (!in_array($matches[2], $blockTags)) {
                        return $this->_wrapBlock($blockJson, $matches[0]);
                    }

                    $html = $matches[1] . '<' . $matches[2] . ' data-debug="' . htmlentities($blockJson) . '"' . $matches[3];

                    return $html;
                },
                $html
            );
        } else {

            $html = $this->_wrapBlock($blockJson, $html);
        }
        $transport->setHtml($html);
    }

    private function _wrapBlock($blockJson, $html)
    {
        $id = self::$id;
        self::$id++;

        $scriptStart = '<script style="display:none;" data-type="djDebug-start" data-id="' . $id . '" data-debug="' . htmlentities($blockJson) . '"></script>';
        $scriptEnd = '<script style="display:none;" data-type="djDebug-end" data-id="' . $id . '"></script>';

        $html = $scriptStart . $html . $scriptEnd;

        return $html;
    }

    /**
     * on eav collection load
     *
     * @param Varien_Event_Observer $event observer
     * @return void
     */
    public function onEavCollectionLoad(Varien_Event_Observer $event)
    {
        if (!$this->isModuleActive) {
            return;
        }

        $collection = $event->getCollection();
        $sqlStruct = array();
        $sqlStruct['sql'] = $collection->getSelectSql(true);
        $sqlStruct['type'] = 'eav';
        $sqlStruct['class'] = get_class($collection);
        $this->collections[] = $sqlStruct;
    }

    /*function onPrepareLayout(Varien_Event_Observer $observer){
        $block = $observer->getEvent()->getBlock();
        var_dump(array_keys($observer->getEvent()->getData()));
        // Mage::log('onPrepareLayout: ' . get_class($observer) . 'block=";

        $layoutUpdate = array();
        $layoutUpdate['block'] = get_class($observer->getBlock());
        $layoutUpdate['name'] = get_class($observer->getName());
        $this->layoutUpdates[] = $layoutUpdate;
    }*/

    /**
     * on model load
     *
     * @param Varien_Event_Observer $observer observer
     * @return Magneto_Debug_Model_Observer
     */
    public function onModelLoad(Varien_Event_Observer $observer)
    {
        if (!$this->isModuleActive) {
            return;
        }

        $event = $observer->getEvent();
        $object = $event->getObject();
        $key = get_class($object);

        if ( array_key_exists($key, $this->models) ) {
            $this->models[$key]['occurences']++;
        } else {
            $model = array();
            $model['class'] = get_class($object);
            $model['resource_name'] = $object->getResourceName();
            $model['occurences'] = 1;
            $this->models[$key] = $model;
        }

        return $this;
    }

    /**
     * We listen to this event to filter access to actions defined by Debug module.
     * We allow only actions if debug toolbar is on and ip is listed in Developer Client Restrictions
     *
     * @param Varien_Event_Observer $observer obeserver
     * @return void
     */
    public function onActionPreDispatch(Varien_Event_Observer $observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        $moduleName = $action->getRequest()->getControllerModule();
        if ( strpos($moduleName, "Magneto_Debug") === 0 && !Mage::helper('debug')->isRequestAllowed() ) {

            Mage::log("Access to Magneto_Debug's actions blocked: dev mode is set to false.");
            // $response = $action->getResponse();
            // $response->setHttpResponseCode(404);
            // $response->setBody('Site access denied.');
            //$action->setDispatched(true)
            //
            exit();
        }
    }

}

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
class Magneto_Debug_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function cleanCache()
    {
        Mage::app()->cleanCache();
    }

    function isRequestAllowed() {
        if (Mage::getIsDeveloperMode() == false ) {
            return false;
        }
        $isDebugEnable = (int)Mage::getStoreConfig('debug/options/enable');
        $clientIp = $this->_getRequest()->getClientIp();
        $allow = false;

        if( $isDebugEnable ){
            $allow = true;

            // Code copy-pasted from core/helper, isDevAllowed method
            // I cannot use that method because the client ip is not always correct (e.g varnish)
            /*$allowedIps = Mage::getStoreConfig('dev/restrict/allow_ips');
            if ( $isDebugEnable && !empty($allowedIps) && !empty($clientIp)) {
                $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
                if (array_search($clientIp, $allowedIps) === false
                    && array_search(Mage::helper('core/http')->getHttpHost(), $allowedIps) === false) {
                    $allow = false;
                }
            }*/
        }

        return $allow;
    }

    function formatSize($size) {
        $sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        if ($size == 0) {
            return('n/a');
        } else {
            return ( round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]);
        }
    }

    public function getMemoryUsage(){
        return $this->formatSize( memory_get_peak_usage(TRUE) );
    }

    public function getScriptDuration(){
        if( function_exists('xdebug_time_index') ){
            return sprintf("%0.2f", xdebug_time_index() );
        } else {
            return 'n/a';
        }
    }

    public static function sortModelCmp($a, $b) {
        if($a['occurences']==$b['occurences'])
            return 0;
        return ($a['occurences'] < $b['occurences']) ? 1 : -1;
    }

    public function sortModelsByOccurences(&$models) {
        usort($models, array('Magneto_Debug_Helper_Data', 'sortModelCmp'));
    }

    public function getBlockFilename($blockClass)
    {
        return mageFindClassFile($blockClass);
    }


    /**
     * Returns all xml files that contains layout updates.
     *
     */
    public function getLayoutUpdatesFiles($storeId=null) {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }
        /* @var $design Mage_Core_Model_Design_Package */
        $design = Mage::getSingleton('core/design_package');
        $updatesRoot = Mage::app()->getConfig()->getNode($design->getArea().'/layout/updates');

        // Find files with layout updates
        $updateFiles = array();
        foreach ($updatesRoot->children() as $updateNode) {
            if ($updateNode->file) {
                $module = $updateNode->getAttribute('module');
                if ($module && Mage::getStoreConfigFlag('advanced/modules_disable_output/' . $module, $storeId)) {
                    continue;
                }
                $updateFiles[] = (string)$updateNode->file;
            }
        }
        // custom local layout updates file - load always last
        $updateFiles[] = 'local.xml';

        return $updateFiles;
    }

    /**
     * convert each string which linking to some class or template to ajax link
     *
     * @param string $xml
     * @return string
     */
    public function xmlEncodeAndAddHovers($xml)
    {
        $xml = preg_replace(
            [
            '/<template>(.*?)<\/template>/ium',
            '/template="(.*?)"/ium',
            '/helper="(.*?)"/ium',
            '/(<.+?type=")(.*?)("[^>]*>)/ium'
            ],
            [
            '<template>{{template=$1}}</template>',
            'template="{{template=$1}}"',
            'helper="{{helper=$1}}"',
            '$1{{block=$2}}$3'
            ],
            $xml
        );

        $xml = htmlspecialchars($xml);

        if (Mage::app()->getStore()->isAdmin()) {
            $routeBase = 'debug/admin_index';
        } else {
            $routeBase = 'debug/index';
        }


        $pattern = '/{{(.*?)}}/ium';
        $xml = preg_replace_callback(
            $pattern,
            function($matches) use ($routeBase) {
                list($key,$val) = explode('=', $matches[1]);

                switch ($key) {
                    case 'block':

                        $url = Mage::getUrl($routeBase . '/viewBlock',
                            [
                            'block' => Mage::app()->getConfig()->getBlockClassName($val),
                            '_secure' => Mage::app()->getStore()->isCurrentlySecure()
                            ]
                        );
                        $result = '<a class="remoteCall" href="'.$url.'">'.$val.'</a>';
                        break;
                   case 'helper':
                        $url = Mage::getUrl($routeBase . '/viewBlock',
                            [
                                'block' => Mage::app()->getConfig()->getHelperClassName(preg_replace('/\/[^\/]*$/', '', $val)) ,
                                '_secure' => Mage::app()->getStore()->isCurrentlySecure()
                            ]
                        );
                        $result = '<a class="remoteCall" href="'.$url.'">'.$val.'</a>';
                        break;
                    case 'template':

                        $block = new Mage_Core_Block_Template();
                        $block->setTemplate($val);

                        $url = Mage::getUrl($routeBase . '/viewTemplate',
                            [
                                '_query' =>
                                [
                                    'template' => $block->getTemplateFile() ,
                                ],
                                '_secure' => Mage::app()->getStore()->isCurrentlySecure()
                                ]
                        );
                        $result = '<a class="remoteCall" href="'.$url.'">'.$val.'</a>';
                        break;
                }
                return $result;
            },
            $xml
        );

        return $xml;
    }

    public function getRootPath()
    {
        return Mage::getStoreConfig('debug/options/root_path');
    }
    public function getDirectorySeparator()
    {
        return Mage::getStoreConfig('debug/options/directory_separator');
    }

    public function fixAbsolutePath($path)
    {
        $root = $this->getRootPath();
        $directory_separator = $this->getDirectorySeparator();

        if (!empty($root)) {
            $path = preg_replace('/^'.preg_quote(Mage::getBaseDir(),'/').'/', $root, $path);
        }
        if (!empty($directory_separator)) {
            $path = str_replace(DIRECTORY_SEPARATOR, $directory_separator, $path);
        }

        return $path;
    }
}

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
 * versions block
 *
 * @category Magneto
 * @package Magneto_Debug
 * @author Misha Medgitov <medgitov@gmail.com>
 */
class Magneto_Debug_Block_Versions extends Magneto_Debug_Block_Abstract
{
    protected function getItems() {
        $items = array();
        $items[] = array(
            'module' => 'Magento',
            'codePool'=> 'core',
            'active'=> true,
            'version'=> Mage::getVersion());

        $modulesConfig = Mage::getConfig()->getModuleConfig();
        foreach ($modulesConfig as $node){
            foreach ($node as $module=>$data) {
                $items[] = array(
                    "module" => $module,
                    "codePool" => $data->codePool,
                    "active" => $data->active,
                    "version" => $data->version
                );
            }
        }

        return $items;
    }
}


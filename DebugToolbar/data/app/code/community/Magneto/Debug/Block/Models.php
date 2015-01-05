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
 * models block
 *
 * @category Magneto
 * @package Magneto_Debug
 * @author Misha Medgitov <medgitov@gmail.com>
 */
class Magneto_Debug_Block_Models extends Magneto_Debug_Block_Abstract
{
    const SQL_SELECT_ACTION = 'viewSqlSelect';
    const SQL_EXPLAIN_ACTION = 'viewSqlExplain';

    protected function getItems() {
    	return Mage::getSingleton('debug/observer')->getModels();
    }

	protected function getQueries() {
		return Mage::getSingleton('debug/observer')->getQueries();
	}

    protected function getCollections() {
        return Mage::getSingleton('debug/observer')->getCollections();
    }


    /**
     * $viewType can be 'Select' or 'Explain'
     */
    protected function getSqlUrl(Zend_Db_Profiler_Query $query, $viewType=self::SQL_SELECT_ACTION) {
        $queryType = $query->getQueryType();
        if( $queryType == Zend_Db_Profiler::SELECT )
        {
            return Mage::getUrl('debug/index/' . $viewType,
                array('_query' => array(
                    'sql'=>$query->getQuery(),
                    'params'=>$query->getQueryParams())
                ));
        } else {
            return '';
        }
    }

    public function getSqlSelectUrl(Zend_Db_Profiler_Query $query) {
        return $this->getSqlUrl($query, self::SQL_SELECT_ACTION);
    }

    public function getSqlExplainUrl(Zend_Db_Profiler_Query $query) {
        return $this->getSqlUrl($query, self::SQL_EXPLAIN_ACTION);
    }
}

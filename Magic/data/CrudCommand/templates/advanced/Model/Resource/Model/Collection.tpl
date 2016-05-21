<?php
class {{model_resource_collection_class_name}}
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('{{module_alias}}/{{model}}');
    }
}

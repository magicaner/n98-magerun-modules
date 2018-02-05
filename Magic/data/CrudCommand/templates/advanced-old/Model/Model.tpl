<?php
class {{model_class_name}}
    extends Mage_Core_Model_Abstract
{
    /**
     * Init the resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('{{module_alias}}/{{model}}');
    }

}

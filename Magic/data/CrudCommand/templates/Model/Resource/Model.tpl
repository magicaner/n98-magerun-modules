<?php
class {{model_resource_class_name}}
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('{{module}}/{{table}}', '{{primarykey}}');
    }

    /**
     * Perform actions before object save
     *
     * @param Mage_Core_Model_Resource_Db_Abstract $object model
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getData('created_at')) {
            $object->setData('created_at', Mage::app()->getLocale()->date()->toString('YYYY-MM-dd HH:mm:ss'));
        }

        return parent::_beforeSave($object);
    }
}

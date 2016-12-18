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
        $this->_init('{{module_alias}}/{{table_alias}}', '{{primarykey}}');
    }

    /**
     * Prepare data for save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return array
     */
    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        $currentTime = Varien_Date::now();
        if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {
            $object->setCreatedAt($currentTime);
        }
        $data = parent::_prepareDataForSave($object);
        return $data;
    }
}

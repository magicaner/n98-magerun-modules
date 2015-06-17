<?php
class {{admin_controller_class_name}} extends Mage_Adminhtml_Controller_Action
{
    /**
     * init action
     *
     * @return void
     */
    public function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('{{menu}}');

        $this->_title($this->__(''));
    }

    /**
     * index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('Manage {{model|ucfirst}}'));

        if ($this->getRequest()->isAjax()) {
            $block = $this->getLayout()->createBlock('{{module_alias}}/{{block_admin_grid}}');
            $this->getResponse()->setBody($block->toHtml());
        } else {
            $block = $this->getLayout()->createBlock('{{module_alias}}/{{block_admin_grid_container}}');
            $this->_addContent($block);
            $this->renderLayout();
        }
    }

    /**
     * edit action
     *
     * @return void
     */
    public function editAction()
    {
        $this->_initAction();
        $this->_title($this->__('Edit'));

        $this->_addContent($this->getLayout()->createBlock('{{module_alias}}/{{block_admin_edit}}'));
        $this->renderLayout();
    }

    /**
     * new action
     *
     * @return void
     */
    public function newAction()
    {
        $this->_initAction();

        $this->_title($this->__('Create'));
        $this->_addContent($this->getLayout()->createBlock('{{module_alias}}/{{block_admin_edit}}'));

        $this->renderLayout();
    }

    /**
     * create new record action
     *
     * @return void
     */
    public function postAction()
    {
        /* @var $helper {{helper_data_class_name}} */
        /* @var $model {{model_class_name}} */
        $helper = Mage::helper('{{module_alias}}/data');

        if ($data = $this->getRequest()->getPost()) {

            $model = Mage::getModel('{{module_alias}}/{{model}}')->setData($data);

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('{{model|ucfirst}} successfully created'));
                $this->getResponse()->setRedirect($this->getUrl('*/*/'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
    }

    /**
     * save action
     *
     * @return void
     */
    public function saveAction()
    {
        /* @var $helper {{helper_data_class_name}} */
        /* @var $model {{model_class_name}} */
        $helper = Mage::helper('{{module_alias}}/data');

        $entityId = $this->getRequest()->getParam('entity_id', false);
        $model = Mage::getModel('{{module_alias}}/{{model}}');

        if ($data = $this->getRequest()->getPost()) {
            try {
                if (!$entityId) {
                    Mage::exception('Mage_Core', $helper->__('Entity id not found'));
                }

                $model->load($entityId);
                if (!$model->getId()) {
                    Mage::exception('Mage_Core', $helper->__('Model instance not found'));
                }

                $model->addData($data);
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('{{model|ucfirst}} successfully saved'));
                $this->getResponse()->setRedirect($this->getUrl('*/*/'));

                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
    }

    /**
     * delete
     *
     * @return void
     */
    public function deleteAction()
    {
        $entityId = $this->getRequest()->getParam('id', false);

        try {
            $model = Mage::getModel('{{module_alias}}/{{model}}')->load($entityId);
            $model->delete();

            Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('{{module_alias}}/data')->__('{{model|ucfirst}} successfully deleted'));
            $this->getResponse()->setRedirect($this->getUrl('*/*/'));

            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirectReferer();
    }
}

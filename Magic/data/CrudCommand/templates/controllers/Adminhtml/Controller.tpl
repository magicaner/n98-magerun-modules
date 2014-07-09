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
        $this->_title($this->__('Manage {{model}}'));

        if ($this->getRequest()->isAjax()) {
            $block = $this->getLayout()->createBlock('{{module}}/{{block_admin_grid}}');
            $this->getResponse()->setBody($block->toHtml());
        } else {
            $block = $this->getLayout()->createBlock('{{module}}/{{block_admin_grid_container}}');
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

        $this->_addContent($this->getLayout()->createBlock('{{module}}/{{block_admin_edit}}'));
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
        $this->_addContent($this->getLayout()->createBlock('{{module}}/{{block_admin_edit}}'));

        $this->renderLayout();
    }

    /**
     * create new record action
     *
     * @return void
     */
    public function postAction()
    {
        /* @var $helper Ecommeleon_Sponsor_Helper_Data */
        /* @var $model Ecommeleon_Sponsor_Model_Invitation */
        $helper = Mage::helper('{{module}}/data');

        if ($data = $this->getRequest()->getPost()) {

            $model = Mage::getModel('{{module}}/{{model}}')->setData($data);

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('{{model}} successfully created'));
                $this->getResponse()->setRedirect($this->getUrl('*/*/'));
                return;
            } catch (Exception $e){
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
        /* @var $helper Ecommeleon_Sponsor_Helper_Data */
        /* @var $model Ecommeleon_Sponsor_Model_RecurringCredit */
        $helper = Mage::helper('{{module}}/data');

        $entityId = $this->getRequest()->getParam('entity_id', false);
        $model = Mage::getModel('{{module}}/{{model}}');

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
                Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('{{model}} successfully saved'));
                $this->getResponse()->setRedirect($this->getUrl('*/*/'));

                return;
            } catch (Exception $e){
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
            $model = Mage::getModel('{{module}}/{{model}}')->load($entityId);
            $model->delete();

            Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('{{module}}/data')->__('{{model}} successfully deleted'));
            $this->getResponse()->setRedirect($this->getUrl('*/*/'));

            return;
        } catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirectReferer();
    }
}

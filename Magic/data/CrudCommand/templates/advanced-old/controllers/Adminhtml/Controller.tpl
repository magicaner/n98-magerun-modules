<?php
class {{admin_controller_class_name}} extends Mage_Adminhtml_Controller_Action
{
    /**
     * is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('path/to/config');
    }

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
        $this->_title($this->__('Manage {{model|uc_words: }}'));

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
        $this->_title($this->__('Edit {{model|uc_words: }}'));

        $entityId = $this->getRequest()->getParam('id', false);
        $storeId = $this->getRequest()->getParam('store', 0);
        $model = Mage::getModel('{{module_alias}}/{{model}}');
        $model->setStoreId($storeId);
        $model->load($entityId);
        Mage::register('{{model}}', $model);

        $storeSwitcher = $this->getLayout()
            ->createBlock('adminhtml/store_switcher');
        $tabs = $this->getLayout()
            ->createBlock('{{module_alias}}/adminhtml_{{model}}_edit_form_tabs');
        $tabsGeneral = $this->getLayout()
            ->createBlock(
                '{{module_alias}}/adminhtml_{{model}}_edit_form_tabs_general',
                'tab_{{model}}_general'
            );
        $tabs->append($tabsGeneral);
        $tabs->addTab('general', 'tab_{{model}}_general');
        $this->getLayout()->getBlock('left')->append($storeSwitcher);
        $this->getLayout()->getBlock('left')->append($tabs);

        $block = $this->getLayout()
            ->createBlock('{{module_alias}}/adminhtml_{{model}}_edit_form');
        $this->_addContent($block);


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

        $this->_title($this->__('Create {{model|uc_words: }}'));

        $storeSwitcher = $this->getLayout()
            ->createBlock('adminhtml/store_switcher');
        $tabs = $this->getLayout()->createBlock('{{module_alias}}/adminhtml_{{model}}_edit_form_tabs');
        $tabsGeneral = $this->getLayout()
            ->createBlock(
                '{{module_alias}}/adminhtml_{{model}}_edit_form_tabs_general',
                'tab_{{model}}_general'
            );
        $tabs->append($tabsGeneral);
        $tabs->addTab('general', 'tab_{{model}}_general');
        $this->getLayout()->getBlock('left')->append($storeSwitcher);
        $this->getLayout()->getBlock('left')->append($tabs);

        $block = $this->getLayout()
            ->createBlock('{{module_alias}}/adminhtml_{{model}}_edit_form');
        $this->_addContent($block);

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

            if (isset($data['{{model}}'])) {
                $data = $data['{{model}}'];
            }

            $model = Mage::getModel('{{module_alias}}/{{model}}')->setData($data);

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess($helper->__('{{model|ucfirst}} has been successfully created'));
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

        $entityId = $this->getRequest()->getParam('id', false);
        $model = Mage::getModel('{{module_alias}}/{{model}}');

        if ($data = $this->getRequest()->getPost()) {
            if (isset($data['{{model}}'])) {
                $data = $data['{{model}}'];
            }
            try {
                if (!$entityId) {
                    Mage::exception('Mage_Core', $helper->__('Entity id not found'));
                }

                $model->load($entityId);
                if (!$model->getId()) {
                    Mage::exception('Mage_Core', $helper->__('{{model|uc_words: }} instance not found'));
                }

                $model->addData($data);
                $model->save();
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess($helper->__('{{model|uc_words: }} has been successfully saved'));
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
                ->addSuccess(Mage::helper('{{module_alias}}/data')->__('{{model|uc_words: }} has been successfully deleted'));
            $this->getResponse()->setRedirect($this->getUrl('*/*/'));

            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirectReferer();
    }

    /**
     * export csv action
     *
     * @return void
     */
    public function exportCsvAction()
    {
        $fileName = 'gifts.csv';
        $content = $this->getLayout()->createBlock('{{module_alias}}/{{block_admin_grid}}')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }
}

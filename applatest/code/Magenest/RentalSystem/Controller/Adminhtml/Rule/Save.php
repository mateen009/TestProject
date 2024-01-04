<?php
namespace Magenest\RentalSystem\Controller\Adminhtml\Rule;

use Magenest\RentalSystem\Model\RentalRule;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\DataObject;

class Save extends AbstractRule implements HttpPostActionInterface
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        try {
            if (!empty($data)) {
                $rentalRuleModel = $this->rentalRule->create();
                if (!empty($data['entity_id'])) {
                    $this->rentalRuleResource->load($rentalRuleModel, $data['entity_id']);
                    if (!$rentalRuleModel->getId()) {
                        $this->messageManager->addErrorMessage(__("The requested rule is no longer existed."));
                        $this->_redirect('rentalsystem/*');
                        return;
                    }
                } elseif (isset($data['entity_id'])) {
                    unset($data['entity_id']);
                }

                $validateResult = $rentalRuleModel->validateData(new DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                    $this->_getSession()->setPageData($data);
                    $this->dataPersistor->set('rentalsystem_rule', $data);
                    $this->_redirect('rentalsystem/*/edit', ['id' => $rentalRuleModel->getId()]);
                    return;
                }

                if (isset($data['rule'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                    $data['apply_all'] = count($data['conditions']) === 1;
                    foreach ($data['conditions'] as $condition) {
                        if (isset($condition['attribute']) && $condition['attribute'] == 'category_ids') {
                            $category_ids[] = explode(', ', $condition['value']);
                        }
                    }
                    $category_ids = !empty($category_ids) ? array_unique(array_merge(...$category_ids)) : [];
                    $data['category_ids'] = !empty($category_ids) ? implode(",", $category_ids) : null;
                    unset($data['rule']);
                } else {
                    $data['apply_all'] = true;
                }

                unset($data['conditions_serialized']);
                unset($data['actions_serialized']);

                $rentalRuleModel->loadPost($data);
                $this->_getSession()->setPageData($data);
                $this->rentalRuleResource->save($rentalRuleModel);

                if (!$this->rentalRuleIndexer->isIndexerScheduled()) {
                    $this->rentalRuleIndexer->reindexRow($rentalRuleModel->getId());
                } else {
                    $this->rentalRuleIndexer->markIndexerAsInvalid();
                }

                $this->messageManager->addSuccessMessage(__('Rental rule is saved.'));
                $this->_getSession()->setPageData(false);
                $this->dataPersistor->clear('rentalsystem_rule');

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('rentalsystem/*/edit', ['id' => $rentalRuleModel->getId()]);
                    return;
                }
                $this->_redirect('rentalsystem/*/');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);

            $this->_getSession()->setPageData($data);
            $this->dataPersistor->set('rentalsystem_rule', $data);

            $param = !empty($data['entity_id']) ? ['id' => $data['entity_id']] : [];
            $this->_redirect('rentalsystem/*/edit', $param);
        }
        $this->_redirect('rentalsystem/*/');
    }
}

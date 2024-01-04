<?php

namespace Cminds\Oapm\Plugin\Customer\Group;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\GroupRegistry;
use Magento\Framework\Exception\NoSuchEntityException;


/**
 * Class GroupRepository
 *
 * @package Cminds\Oapm\Plugin\Customer\Group
 */
class GroupRepository
{
    const GROUP_MANAGER_EMAIL_CODE = 'group_manager_email';
    /**
     * @var GroupRegistry
     */
    protected $groupRegistry;

    /**
     * GroupRepository constructor.
     * @param GroupRegistry $groupRegistry
     */
    public function __construct(
        GroupRegistry $groupRegistry
    ) {
        $this->groupRegistry = $groupRegistry;
    }

    /**
     * @param GroupRepositoryInterface $subject
     * @param $result
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterGetById(GroupRepositoryInterface $subject, $result)
    {
        if (!empty($result) && !empty($result->getId())) {
            $groupModel = $this->groupRegistry->retrieve($result->getId());
            $result->setData(
                self::GROUP_MANAGER_EMAIL_CODE,
                $groupModel->getData(self::GROUP_MANAGER_EMAIL_CODE)
            );
        }
        return $result;
    }
}

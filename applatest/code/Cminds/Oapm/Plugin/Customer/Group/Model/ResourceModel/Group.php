<?php

namespace Cminds\Oapm\Plugin\Customer\Group\Model\ResourceModel;

use Cminds\Oapm\Plugin\Customer\Group\GroupRepository;
use \Magento\Customer\Model\ResourceModel\Group as ParentGroup;
use Magento\Framework\App\RequestInterface;

/**
 * Class Group
 *
 * @package Cminds\Oapm\Plugin\Customer\Group\Model\ResourceModel
 */
class Group
{
    /**
     * Retrieve request object
     *
     * @return RequestInterface
     */
    protected $request;

    /**
     * GroupRepository constructor.
     * @param $request RequestInterface
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param ParentGroup $subject
     * @param $group
     * @return array
     */
    public function beforeSave(ParentGroup $subject, $group)
    {
        $email = $this->request->getParam(GroupRepository::GROUP_MANAGER_EMAIL_CODE);
        $group->setData(GroupRepository::GROUP_MANAGER_EMAIL_CODE, $email);
        return [$group];
    }
}

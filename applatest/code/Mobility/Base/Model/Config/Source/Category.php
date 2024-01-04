<?php

namespace Mobility\Base\Model\Config\Source;

class Category extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $_categoryHelper;
    protected $categoryRepository;
    protected $categoryList;

    public function __construct(
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository
    ) {
        $this->_categoryHelper = $catalogCategory;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $categories = $this->getCategoryOptions();
            if ($categories) {
                foreach ($categories as $key => $value) {
                    $this->_options[] = ['label' => $value, 'value' => $key];
                }
            } else {
                $this->_options[] = ['label' => '', 'value' => ''];
            }
        }

        return $this->_options;
    }

    /*
     * Return categories helper
     */

    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted, $asCollection, $toLoad);
    }

    /*
     * Get options in "key-value" format
     * @return array
     */
    public function getCategoryOptions()
    {
        $categories = $this->getStoreCategories(true, false, true);
        $categoryList = $this->renderCategories($categories);
        // echo "<pre>";
        // print_r($categories->getData());die;
        return $categoryList;
    }

    public function renderCategories($_categories)
    {
        foreach ($_categories as $category) {
            $i = 0;
            $this->categoryList[$category->getEntityId()] = __($category->getName());   // Main categories
            $list = $this->renderSubCat($category, $i);
        }

        return $this->categoryList;
    }

    public function renderSubCat($cat, $j)
    {
        $categoryObj = $this->categoryRepository->get($cat->getId());

        $level = $categoryObj->getLevel();
        $arrow = str_repeat(" -- ", $level - 1);
        $subcategories = $categoryObj->getChildrenCategories();

        foreach ($subcategories as $subcategory) {
            $this->categoryList[$subcategory->getEntityId()] = __($arrow . $subcategory->getName());
            if ($subcategory->hasChildren()) {
                $this->renderSubCat($subcategory, $j);
            }
        }

        return $this->categoryList;
    }
}

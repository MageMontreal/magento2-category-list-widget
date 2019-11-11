<?php
namespace MageMontreal\CategoryWidget\Block\Widget;

class CategoryWidget extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'widget/categorywidget.phtml';

    const DEFAULT_IMAGE_WIDTH = 250;
    const DEFAULT_IMAGE_HEIGHT = 250;

    /**
     * \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    protected $_categoryFactory;

    /**
     * \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magento\Catalog\Model\CategoryFactory $categoryFactory,
    \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
    array $data = []
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current store categories
     *
     * @return \Magento\Framework\Data\Tree\Node\Collection|\Magento\Catalog\Model\Resource\Category\Collection|array
     */
    public function getCategoryCollection()
    {
        $category = $this->_categoryFactory->create();

        $childCategories = $this->_categoryCollectionFactory->create();
        if ($this->getData('childrencat')) {
            $categoryIds = array_map('trim',explode(',', $this->getData('childrencat')));
        }
        else {
            if ($this->getData('parentcat') > 0) {
                $rootCatID = $this->getData('parentcat');
            } else {
                $rootCatID = $this->_storeManager->getStore()->getRootCategoryId();
            }
            $category->load($rootCatID);
            $categoryIds = $category->getChildrenCategories();
        }

        $childCategories
            ->addAttributeToFilter('entity_id', ['in' => $categoryIds])
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToSelect(['name', 'image'])
            ->setOrder('position', 'ASC');

        if($this->getMenuOnly()) {
            $childCategories->addAttributeToFilter('include_in_menu', 1);
        }

        return $childCategories;
    }

    private function getMenuOnly()
    {
        if (empty($this->getData('menu_only'))) {
            return true;
        }
        return $this->getData('menu_only') === '1';
    }

    /**
     * Get the width of product image
     * @return int
     */
    public function getImageWidth()
    {
        if (empty($this->getData('imagewidth'))) {
            return self::DEFAULT_IMAGE_WIDTH;
        }
        return (int) $this->getData('imagewidth');
    }

    /**
     * Get the height of product image
     * @return int
     */
    public function getImageHeight()
    {
        if (empty($this->getData('imageheight'))) {
            return self::DEFAULT_IMAGE_HEIGHT;
        }
        return (int) $this->getData('imageheight');
    }

    public function canShowImage()
    {
        return in_array($this->getData('display'), ['image', 'image-name']);
    }

    public function canShowName()
    {
        return in_array($this->getData('display'), ['name', 'image-name']);
    }
}
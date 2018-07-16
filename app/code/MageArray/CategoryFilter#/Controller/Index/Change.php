<?php

namespace MageArray\CategoryFilter\Controller\Index;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Change extends Action
{

	/**
	 * Change constructor.
	 * @param Context $context
	 * @param StoreManagerInterface $storeManager
	 * @param CategoryFactory $categoryFactory
	 * @param CategoryRepositoryInterface $categoryRepository
     */
	public function __construct(
		Context $context, 
		StoreManagerInterface $storeManager,
		CategoryFactory $categoryFactory,
		CategoryRepositoryInterface $categoryRepository
	) 
    {
		$this->_storeManager = $storeManager;
		$this->_categoryInstance = $categoryFactory->create();
		$this->categoryRepository = $categoryRepository;
        parent::__construct($context);    
    }

	/**
	 *
     */
	public function execute()
    {
		$category = $this->_categoryInstance;
		$catId = $this->getRequest()->getParam('selectedValue');
        $categories = $category->getCategories($catId, 2);
		$categoryArray = [];
		$i = 0;
        foreach ($categories as $category) {
            $categoryArray[$i]['id'] = $category->getId();
            $categoryArray[$i]['name'] = $category->getName();
			$category = $this->categoryRepository->get($category->getId(), $this->_storeManager->getStore()->getId());
            $categoryArray[$i]['url'] = $category->getUrl();
			$i++;
        }
		echo json_encode($categoryArray);
    }
}

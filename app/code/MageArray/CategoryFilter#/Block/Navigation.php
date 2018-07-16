<?php
namespace MageArray\CategoryFilter\Block;

use Magento\Catalog\Helper\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;

class Navigation extends \Magento\Catalog\Block\Navigation implements BlockInterface
{

    protected $_categoryInstance;

    protected $_catalogCategory;

    protected $_registry;

    protected $_httpContext;

    protected $_productCollectionFactory;

    protected $_flatState;

    /**
     * Navigation constructor.
     * @param Context $context
     * @param CategoryFactory $categoryFactory
     * @param CollectionFactory $productCollectionFactory
     * @param Resolver $layerResolver
     * @param HttpContext $httpContext
     * @param Category $catalogCategory
     * @param Registry $registry
     * @param State $flatState
     * @param array $data
     */
    public function __construct (
        Context $context ,
        CategoryFactory $categoryFactory ,
        CollectionFactory $productCollectionFactory ,
        Resolver $layerResolver ,
        HttpContext $httpContext ,
        Category $catalogCategory ,
        Registry $registry ,
        State $flatState ,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_httpContext = $httpContext;
        $this->_catalogCategory = $catalogCategory;
        $this->_registry = $registry;
        $this->_flatState = $flatState;
        $this->_categoryInstance = $categoryFactory->create ();
        parent::__construct ( $context , $categoryFactory , $productCollectionFactory , $layerResolver , $httpContext ,
            $catalogCategory , $registry , $flatState , $data );
    }

    /**
     *
     */
    public function _construct ()
    {
        parent::_construct ();
        $template = $this->getData ( 'template' );
        $this->setTemplate ( $template );
    }

    /**
     * @return mixed
     */
    public function getTitle ()
    {
        return $this->getData ( 'title' );
    }

    /**
     * @return mixed
     */
    public function getLevels ()
    {
        return $this->getData ( 'levels' );
    }

    /**
     * @return mixed
     */
    public function getLabelsEmbedded ()
    {
        return $this->getData ( 'labels_embedded' );
    }

    /**
     * @return array
     */
    public function getSelectLabels ()
    {
        $labels = array ();
        foreach (explode ( "," , $this->getData ( 'select_labels' ) ) as $label) {
            $labels[] = __ ( $label );
        }
        return $labels;
    }

    /**
     * @param $i
     * @return mixed
     */
    public function getSelectLabel ($i)
    {
        $labels = $this->getSelectLabels ();
        if (isset($labels[$i])) {
            return __ ( $labels[$i] );
        } else {
            return __ ( 'Select category' );
        }
    }

    /**
     * @return mixed
     */
    public function getBaseUrl ()
    {
        return $this->_storeManager->getStore ()->getBaseUrl ();
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo ()
    {
        $shortCacheId = [
            'CATEGORY_FILTER' ,
            $this->_storeManager->getStore ()->getId () ,
            $this->_design->getDesignTheme ()->getId () ,
            $this->_httpContext->getValue ( Context::CONTEXT_GROUP ) ,
            'template' => $this->getTemplate () ,
            'name' => $this->getNameInLayout () ,
            $this->getCurrentCategoryKey () ,
            $this->getRootCategory () ,
            $this->getLevels ()
        ];
        $cacheId = $shortCacheId;

        $shortCacheId = array_values ( $shortCacheId );
        $shortCacheId = implode ( '|' , $shortCacheId );
        $shortCacheId = md5 ( $shortCacheId );

        $cacheId['category_path'] = $this->getCurrentCategoryKey ();
        $cacheId['short_cache_id'] = $shortCacheId;

        return $cacheId;
    }

    /**
     * @return array
     */
    public function getConfigJson ()
    {
        if ($this->getLabelsEmbedded () == 'outside') {
            $label = "";
        } else {
            $label = $this->getLabelsEmbedded ();
        }
        $config = array (
            'levels' => $this->getLevels () ,
            'id' => 'cd-' . $this->getNameInLayout () ,
            'current_category_id' => ($this->_registry->registry ( 'current_category' ) ? $this->_registry->registry ( 'current_category' )->getId () : 0) ,
            'fetch_children_url' => $this->getUrl ( 'categoryfilter/ajax/fetchChildren' ) ,
            'labels' => $this->getSelectLabels () ,
            'default_label' => __ ( 'Select category' ) ,
            'labels_embedded' => $label ,
            'please_wait_text' => __ ( 'Please wait...' ) ,
        );
        $categories = $this->getCategoryTree ();
        $config['categories'] = array ();
        foreach ($categories as $category) {
            $config['categories'][] = $this->getCategoryConfig ( $category );
        }
        if ($this->getFetchMode () == 'ajax') {
            $this->_addActiveCategories ( $config['categories'] , $this->getLevels () - 1 );
        }

        return $config;
    }

    /**
     * @param $category
     * @return array
     */
    public function getCategoryConfig ($category)
    {
        $categoryConfig = $this->_getCategoryDataArray ( $category );
        $children = $category->getChildren ();
        foreach ($children as $child) {
            $categoryConfig['children'][] = $this->getCategoryConfig ( $child );
        }
        return $categoryConfig;
    }

    /**
     * @return mixed
     */
    public function getCategoryTree ()
    {
        $category = $this->_categoryInstance;
        if (!$this->hasData ( 'root_id' )) {
            $rootPath = explode ( '/' , $this->_getData ( 'root_category' ) );
            $this->setRootId ( $rootPath[1] );
        }
        $this->_registry->unregister ( '_resource_singleton/catalog/category_flat' );
        $recursionLevel = ($this->getFetchMode () == 'preload') ? $this->getLevels () : 1;
        $categories = $category->getCategories ( $this->getRootId () , $recursionLevel );
        return $categories;
    }

    /**
     * @param $category
     * @return array
     */
    protected function _getCategoryDataArray ($category)
    {
        return array (
            'id' => $category->getId () ,
            'name' => $this->escapeHtml ( $category->getName () ) ,
            'url' => $this->getCategoryUrl ( $category ) ,
            'active' => $this->isCategoryActive ( $category ) ,
            'children' => array ()
        );
    }

    /**
     * @param $categories
     * @param $levels
     */
    protected function _addActiveCategories (&$categories , $levels)
    {
        if ($levels == 0) {
            return;
        }
        foreach ($categories as $key => $child) {
            if ($child['active'] && count ( $child['children'] ) == 0) {
                $category = $this->_categoryInstance;
                foreach ($category->load ( $child['id'] )->getChildrenCategories () as $category) {

                    $categories[$key]['children'][] = $this->_getCategoryDataArray ( $category );
                }
                $this->_addActiveCategories ( $categories[$key]['children'] , $levels - 1 );
            }
        }
    }

}

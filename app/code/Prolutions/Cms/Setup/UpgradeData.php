<?php

namespace Prolutions\Cms\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;
    protected $_blockFactory;

    /**
     * Construct
     *
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     */
    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_blockFactory = $blockFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            $identifier = 'home';
            $title = 'Home Page';

            $content = <<<HTML
<div class="hero hero-home">
<div class="row">
<div class="left-container">
<p class="hero-title">Quality Products</p>
<hr />
<p class="thin-title">At Great Prices</p>
<p class="blue-bar">Hassle-Free Returns &middot; Factory Direct Pricing &middot; Best Price Guarantee</p>
</div>
<div class="right-container">
<div class="free-shipping blue-box">
<p class="title">Free Shipping Offer</p>
<p class="subtitle">on All Orders Over $100 &middot; No Hidden Fees</p>
</div>
<div class="guarantee blue-box">
<p class="title">Custom Fit Guarantee</p>
<p class="subtitle">Guaranteed Cover Fit &middot; Hassle Free Exchanges</p>
</div>
</div>
</div>
</div>
<div class="hero sale-banner">
<div class="row"><img src="{{view url="images/imgpsh_fullsize.png"}}" /></div>
</div>
<div class="row">{{block class="Prolutions\HomeCategories\Block\Grid" template="grid.phtml"}}</div>
<div class="row two-columns">
<div class="column-left">
<p class="title">Why buy from us?</p>
<div class="item">
<div class="icon-container"><img src="{{view url="images/support-icon.png"}}" /></div>
<p class="item-title">Support Team</p>
<p class="description">Instantly chat with our cover experts from 9am - 6pm EST.</p>
</div>
<div class="item">
<div class="icon-container"><img src="{{view url="images/book-icon.png"}}" /></div>
<p class="item-title">Best Materials</p>
<p class="description">Quality products with fabrics like Sunbrella and Sundura.</p>
</div>
<div class="item">
<div class="icon-container"><img src="{{view url="images/truck-icon.png"}}" /></div>
<p class="item-title">Free Shipping</p>
<p class="description">On all covers over $100.</p>
</div>
<div class="item">
<div class="icon-container"><img src="{{view url="images/returns-icon.png"}}" /></div>
<p class="item-title">Easy Returns</p>
<p class="description">Hassel-free returns and exchanges. Receive a refund back to your original payment method.</p>
</div>
</div>
<div class="column-right">{{block class="Prolutions\HomeReviews\Block\Reviews" template="reviews.phtml"}}</div>
</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '1column',
                'stores'    => array(0),
                'content'   => $content
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $identifier = 'cover_world_footer_links';
            $title = 'Cover World Footer Links';

            $content = <<<HTML
<ul class="footer-links">
<li class="item-links-list first">
<p class="title">Cover Types</p>
<ul class="links-list">
<li class="item"><a href="#">Boat Covers</a></li>
<li class="item"><a href="#">Pontoon Covers</a></li>
<li class="item"><a href="#">Jet Ski Covers</a></li>
<li class="item"><a href="#">Bimini Tops</a></li>
<li class="item"><a href="#">Fishing Boat Covers</a></li>
</ul>
</li>
<li class="item-links-list">
<p class="title">Policies</p>
<ul class="links-list">
<li class="item"><a href="#">Return Policy</a></li>
<li class="item"><a href="#">Cancellation Policy</a></li>
<li class="item"><a href="#">Warranty Policy</a></li>
<li class="item"><a href="#">Shipping Policy</a></li>
<li class="item"><a href="#">Privacy Policy</a></li>
</ul>
</li>
<li class="item-links-list">
<p class="title">Help</p>
<ul class="links-list">
<li class="item"><a href="#">FAQs</a></li>
<li class="item"><a href="#">My Account</a></li>
<li class="item"><a href="#">Contact Us</a></li>
<li class="item"><a href="#">Order Status</a></li>
<li class="item"><a href="#">Return Center</a></li>
</ul>
</li>
<li class="item-links-list last">
<p class="title">Our Promise</p>
<ul class="links-list">
<li class="item"><a href="#">Sure-Fit Guarantee</a></li>
<li class="item"><a href="#">Hassle-Free Returns</a></li>
<li class="item"><a href="#">Factory Direct Pricing</a></li>
<li class="item"><a href="#">Largest Online Inventory</a></li>
<li class="item"><a href="#">Safe &amp; Secure Shopping</a></li>
</ul>
</li>
</ul>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $identifier = 'header_banner';
            $title = 'Header Banner';

            $content = <<<HTML
<div class="hero header-banner" style="background-image: url('{{view url="images/home-middle-banner.png"}}');">
<div class="row"><img src="{{view url="images/imgpsh_fullsize.png"}}" /></div>
</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $identifier = 'faq';
            $title = 'FAQs';

            $content = <<<HTML
<div class="title">
<h1>FAQs</h1>
</div>
<div class="faq-item">
<div class="question">
<p>How do I contact you?</p>
</div>
<div class="answer-container">
<div class="answer">You can contact us anytime through the contact form located on our contact page. Our office hours are Monday through Friday 9am-5pm EST and we generally respond to all inquires within 24 hours.</div>
</div>
</div>
<div class="faq-item">
<div class="question">
<p>What payment methods are accepted?</p>
</div>
<div class="answer-container">
<div class="answer">We accept all major credit cards, including American Express, VISA, MasterCard, and Discover. We also accept PayPal and Google Checkout.</div>
</div>
</div>
<div class="faq-item">
<div class="question">
<p>How much does shipping generally cost?</p>
</div>
<div class="answer-container">
<div class="answer">Ground shipping is free for all orders over $100 shipped within the contiguous united States. However, due to the high cost of shipping to Hawaii, Alaska, and US Territories, an additional shipping and handling cost is applied to these areas. This charge varies depending on where the package is being shipped and the exact cost is calculated and displayed during the checkout process before an order is completed. Overnight, 2 Day, and 3 Day shipping options are not eligible for free shipping. Please refer to our shipping policy for additional information.</div>
</div>
</div>
<div class="faq-item">
<div class="question">
<p>Is it safe to use my credit card online to make a purchase?</p>
</div>
<div class="answer-container">
<div class="answer">We work to protect the security of your information during transmission by using Secure Socket Layer (SSL) by COMODO. When you place an order online, the SSL scrambles and encrypts your information before it is sent to us over the Internet. This protects your personal and credit card information from being read while it is transferred through cyberspace. Information exchanged with any address beginning with https is encrypted using SSL before transmission. After your order is placed, your credit card information remains encrypted at all times. We are never able to view your complete credit card information. Only your card type, the last 4 digits, and the expiration date are made available to our sales and support staff.</div>
</div>
</div>
<div class="faq-item">
<div class="question">
<p>Can I get a tracking number and check the status of my order online?</p>
</div>
<div class="answer-container">
<div class="answer">When your order is shipped, you will receive an email to the email address associated with the order which contains a tracking number. You can check the status of your order at any time by visiting our order status page.</div>
</div>
</div>
<div class="faq-item">
<div class="question">
<p>How long does it generally take to receive my order?</p>
</div>
<div class="answer-container">
<div class="answer">Orders are generally processed within 24-72 hours from the time payment is received. FedEx and UPS Ground generally take 2-7 business days to deliver the package from the time it ships from our warehouse.</div>
</div>
</div>
<div class="faq-item">
<div class="question">
<p>Can I cancel or change a recent order?</p>
</div>
<div class="answer-container">
<div class="answer">To cancel or make changes to your order, please contact us from our contact us page and we will do our best to accommodate your request.</div>
</div>
</div>
<div class="faq-item">
<div class="question">
<p>What is your return policy?</p>
</div>
<div class="answer-container">
<div class="answer">All return and exchange requests must be made within 30 days of receiving your product. Please refer to our return policy for additional information.</div>
</div>
</div>
<div class="faq-item">
<div class="question">
<p>Do you accept international orders?</p>
</div>
<div class="answer-container">
<div class="answer">Unfortunately, at this time we can only accept orders from the United States and Canada.</div>
</div>
</div>
<script type="text/javascript" xml="space">// <![CDATA[
// 
require([
    'jquery'
], function ($) {
    $(function() {
 
    $('.question').click(function() {
 
        if ($(this).parent().is('.open')){
            $(this).closest('.faq-item').find('.answer-container').animate({'height':'0'},500);
            $(this).closest('.faq-item').removeClass('open');
 
            }else{
                var newHeight =$(this).closest('.faq-item').find('.answer').height() +'px';
                $(this).closest('.faq-item').find('.answer-container').animate({'height':newHeight},500);
                $(this).closest('.faq-item').addClass('open');
            }
 
    });
 
});
});
// 
// ]]></script>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $identifier = 'returns-exchanges';
            $title = 'Returns & Exchanges';

            $content = <<<HTML
<div class="title">
<h1>RETURNS &amp; EXCHANGES</h1>
</div>
<div class="returns-item">
<div class="question">
<p>Making a Return? No Sweat.</p>
</div>
<div class="answer making-return">
<div class="img-container"><img src="{{view url="images/return-exchange-icon.png"}}" /></div>
<p>We're sorry your order didn't work out, so let us make things right. First, read through our Returns Policy, then get the ball rollin' on your online return.</p>
<p><a href="#">Questions?</a></p>
<div class="clearfix"></div>
</div>
</div>
<div class="returns-item">
<div class="question">
<p>You've Got 3 Simple Options:</p>
</div>
<div class="answer simple-options">
<ul>
<li class="first">
<div class="item-title">
<div class="number">1</div>
<p>Store Credit + <span class="green">$5 Bonus</span>!</p>
</div>
<p>Receive a store credit for the amount of your return, get a FREE return shipping label&ndash;and a BONUS $5 credit you can use toward finding something else you'll love</p>
<p><a href="#">See full details</a></p>
</li>
<li>
<div class="item-title">
<div class="number">2</div>
<p>Refund</p>
</div>
<p>Receive a refund back to your original payment method, minus a $5.99 shipping fee when you use our shipping label.</p>
<p><a href="#">See full details</a></p>
</li>
<li class="last">
<div class="item-title">
<div class="number">3</div>
<p>Exchanges</p>
</div>
<p>Love it but need a different size, color, or pattern? We're happy to exchange as long as that item's still in stock.</p>
<p><a href="#">See full details</a></p>
</li>
</ul>
</div>
</div>
<div class="returns-item">
<div class="question">
<p>Our Returns &amp; Exchange Policy</p>
</div>
<div class="answer policy">
<p class="bold">Eligibilty Window:</p>
<p>To qualify for a return, items must be received back to our Fulfillment Center within 30 days of your original shipment day (as printed on your packing slip). Exchanges must be received within 90 days.</p>
</div>
<div class="answer policy">
<p class="bold">Items Not Eligible for Refund or Store Credit:</p>
<ul>
<li>
<p>Items marked as final sale</p>
</li>
<li>
<p>Returns received in damaged/worn condition.</p>
</li>
<li>
<p>Returns received after 90 days of the original shipment day.</p>
</li>
<li>
<p>Shipping charges, except in cases where we have made a shipping error.</p>
</li>
</ul>
</div>
<div class="answer policy">
<p class="bold">Items Eligible for Store Credit Only:</p>
<p>Returns received by between 31 and 90 days after the original order shipment date (as printed on your packing slip). Late returns will have the $5.99 shipping fee deducted and are not eligible for the $5 bonus offer. One-of-a-kind items (including vintage) are returnable for store credit only.</p>
</div>
<div class="answer policy">
<p class="bold">Packaging Requirements:</p>
<p>Items must be returned with original packaging intact, otherwise they are non-refundable and will receive no refund or store credit.</p>
</div>
<div class="answer policy">
<p class="bold">How do I return an item I received as a gift?</p>
<p>If you received an order as a gift, you can still return! We'll issue you a store credit for the amount of the returned item(s) to your account. Please contact <a href="&quot;#">"Customer Care</a> to get your free shipping label.</p>
<p><a href="#">See full details</a></p>
</div>
<div class="answer policy">
<p class="bold">What happens if I return an item that I bought on sale or used a promo code on?</p>
<p>Coupons and discount codes are allocated across all applicable items. If you applied a coupon or discount code on the order you're returning, OR if you are returning an item purchased on sale, you'll be refunded the amount that you paid after the applied discount.</p>
</div>
<div class="answer policy">
<p class="bold">What if there was something wrong with my order?</p>
<p>Call us right away so we can make things right. Reach us at 888.619.6852.</p>
</div>
</div>
<div class="returns-item">
<div class="question">
<p>How to Make Your Return or Exchange:</p>
</div>
<div class="answer make-your-return">
<div class="number">1</div>
<span class="bold">Step 1</span>
<p>Select your order, select your item(s), and let us know if you'd like to return (be sure to indicate for store credit or refund) or exchange (be sure to indicate your new different size/color/pattern).</p>
<p><a href="#">Start Here</a></p>
</div>
<div class="answer make-your-return">
<div class="number">2</div>
<span class="bold">Step 2</span>
<p>Print your shipping label. If you've selected 'Store Credit' or 'Exchange,' it's free! If you selected 'Refund,' we'll deduct $5.99 from your refund amount to cover the shipping fees.</p>
</div>
<div class="answer make-your-return">
<div class="number">3</div>
<span class="bold">Step 3</span>
<p>Pack it up. Place your item(s) (in unworn condition and with tags attached) and the order invoice in a securely sealed shipping package. (If you misplaced your invoice, simply go to My Account, click on your Order History, select the right order number, and print it out.)</p>
</div>
<div class="answer make-your-return">
<div class="number">4</div>
<span class="bold">Step 4</span>
<p>Send it off. Affix your printed shipping label to your package (making sure there are no other shipping labels or barcodes on it). Jot down the tracking number so you can follow its journey.</p>
<p>Return your package via the US Postal Service or FedEx.</p>
</div>
</div>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $identifier = 'reviews';
            $title = 'Reviews';

            $content = <<<HTML
<div class="title">
<h1>REVIEWS</h1>
</div>
<p>Cover.World is one of most reviewed and highest rated boat cover, pontoon cover, and jet-ski cover retailers in the world. While others might claim to be the best source for boat covers, Cover.World has more reviews from actual, honest, paying customers. Why would you buy from anyone else?</p>
<p>Don't just take our word for it. Read through some of the thousands of reviews that our customers have taken the time to submit to the independent review websites.</p>
<div class="reviews-container">
<p class="bold title">Independent Review Websites</p>
<p class="bold"><a href="#">PriceGrabber</a>: 2,500+ Reviews</p>
<div class="review">
<div class="stars"><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /></div>
<p>"Super easy and Quick. Probably spent less than 5 minutes in your website. Great place!"</p>
</div>
<div class="review">
<div class="stars"><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-empty.png"}}" /></div>
<p>"I didn't know exactly which cover to select so I contacted customer service and they were very helpful."</p>
</div>
<div class="review">
<div class="stars"><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /></div>
<p>"Site was easy to navigate. Was able to find a cover to fit on a older model boat. Great products."</p>
</div>
<div class="review">
<div class="stars"><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /></div>
<p>"The website was well organized and easy to navigate. Quality product and fast shipping."</p>
</div>
<div class="review last">
<div class="stars"><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /></div>
<p>"Found boat cover with no trouble. Was guided through selection process with ease."</p>
</div>
</div>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        if (version_compare($context->getVersion(), '1.0.6') < 0) {
            $identifier = 'boat_covers_finder';
            $title = 'Boat Covers Finder';

            $content = <<<HTML
<div class="boat-top-banner">
<div class="finder-container"><img class="start-here" src="{{view url="images/boat-covers/START_HERE.png"}}" />
<p class="title">Select Your Boat</p>
<p>{{block class="Amasty\Finder\Block\Form" block_id="finder_form" id="1"}}</p>
</div>
{{block class="Magento\\Cms\\Block\\Block" block_id="why_shop"}}</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();


            $identifier = 'why_shop';
            $title = 'Why Shop With Us';

            $content = <<<HTML
<div class="why-shop">
<p class="title">Why shop with us?</p>
<p class="item">Best Price Guarantee</p>
<p class="item">Custom Fit</p>
<p class="item">Safe &amp; Secure</p>
<p class="item">Made in the USA</p>
</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();


            $identifier = 'category_left_column';
            $title = 'Category Left Column';

            $content = <<<HTML
<div class="banner"><img src="{{view url="images/made_in_usa.png"}}" /></div>
<div class="banner"><a href="#"><img src="{{view url="images/best_price_banner.png"}}" /></a></div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();

            $identifier = 'boat_covers_tabs';
            $title = 'Boat Covers Tabs';

            $content = <<<HTML
<div class="info-tabs">
<div class="tab active">
<p>The Importance of Boat Covers</p>
</div>
<div class="content active">
<p>Boats are ingrained into our culture as being synonymous with leisure time. Whether you love to spend time with friends on your ski-boat, relax with family on your pontoon boat, or spend some alone time fishing, we know how important your boat is to you. And like all important things in your life, you want to protect it. Many people choose to use a boat cover to provide the necessary protection from sun, water, and debris.</p>
<p>Our boat covers come in all makes and models &ndash; literally! We have covers to suit the needs of nearly every individual. Regardless of whether you are searching for basic protection from the elements, or a custom cover made from top-of-the-line fabric, we can exceed your expectations.</p>
<p>We understand that all boat covers are not created equally. Some are better suited for trailering, others are great for use while mooring at the lake, and still others work well for indoor storage when not being used on the ocean. We strive to provide comprehensive and easy to find information, so that selecting the appropriate cover for your needs and lifestyle is a breeze.</p>
<p>Protect your boat with one of our covers to keep your investment in pristine condition for years to come!</p>
</div>
<div class="tab">
<p>Best-Price Guarantee</p>
</div>
<div class="content">
<p>Lorem ipsum BESTPRICE</p>
</div>
<div class="tab">
<p>Custom-Fit</p>
</div>
<div class="content">
<p>Lorem ipsum CUSTOM Fit</p>
</div>
</div>
<script type="text/javascript" xml="space">// <![CDATA[
// 
require([
    'jquery'
], function ($) {
    $(function() {
 
    $('.tab').click(function() {
        $('.tab').removeClass('active');
        $('.content').removeClass('active');
        $(this).addClass('active');
        $(this).next().addClass('active');
 
    });
 
});
});
// ]]></script>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            $identifier = 'no-route';
            $title = '404 Not Found';

            $content = <<<HTML
<div class="hero main-404">
<div class="content-404">
<p class="title">WHOOPS!</p>
<p class="subtitle">We couldn't find that page</p>
<a class="back-to-home" href="{{config path="web/unsecure/base_url"}}"><p>Head back to home page</p></a>
</div>
</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '1column',
                'stores'    => array(0),
                'content'   => $content
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        if (version_compare($context->getVersion(), '1.0.8') < 0) {
            $identifier = 'home';
            $title = 'Home Page';

            $content = <<<HTML
<div class="hero hero-home">
<div class="row">
<div class="left-container">
<p class="hero-title">Custom Fit Marine Covers</p>
<hr />
<p class="thin-title">At Great Prices</p>
<ul class="blue-bar">
<li>Hassle-Free Returns</li>
<li>Factory Direct Pricing</li>
<li class="last">Best Price Guarantee</li>
</ul>
</div>
<div class="right-container">
<div class="free-shipping blue-box">
<p class="title">Free Shipping Offer</p>
<p class="subtitle">on All Orders Over $100 &middot; No Hidden Fees</p>
</div>
<div class="guarantee blue-box">
<p class="title">Custom Fit Guarantee</p>
<p class="subtitle">Guaranteed Cover Fit &middot; Hassle Free Exchanges</p>
</div>
</div>
</div>
</div>
<div class="hero sale-banner">
<div class="row"><img src="{{view url="images/imgpsh_fullsize.png"}}" /></div>
</div>
<div class="row">{{block class="Prolutions\HomeCategories\Block\Grid" template="grid.phtml"}}</div>
<div class="row two-columns">
<div class="column-left">
<p class="title">Why buy from us?</p>
<div class="item">
<div class="icon-container"><img src="{{view url="images/support-icon.png"}}" /></div>
<p class="item-title">Support Team</p>
<p class="description">Instantly chat with our cover experts from 9am - 6pm EST.</p>
</div>
<div class="item">
<div class="icon-container"><img src="{{view url="images/book-icon.png"}}" /></div>
<p class="item-title">Best Materials</p>
<p class="description">Quality products with fabrics like Sunbrella and Sundura.</p>
</div>
<div class="item">
<div class="icon-container"><img src="{{view url="images/truck-icon.png"}}" /></div>
<p class="item-title">Free Shipping</p>
<p class="description">On all covers over $100.</p>
</div>
<div class="item">
<div class="icon-container"><img src="{{view url="images/returns-icon.png"}}" /></div>
<p class="item-title">Easy Returns</p>
<p class="description">Hassel-free returns and exchanges. Receive a refund back to your original payment method.</p>
</div>
</div>
<div class="column-right">{{block class="Prolutions\HomeReviews\Block\Reviews" template="reviews.phtml"}}</div>
</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '1column',
                'stores'    => array(0),
                'content'   => $content,
                'content_heading' => 'Home Page'
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();

            $identifier = 'reviews';
            $title = 'Reviews';

            $content = <<<HTML
<div class="title">
<h1>REVIEWS</h1>
</div>
<p>Cover.World is one of most reviewed and highest rated boat cover, pontoon cover, and jet-ski cover retailers in the world. While others might claim to be the best source for boat covers, Cover.World has more reviews from actual, honest, paying customers. Why would you buy from anyone else?</p>
<p>Don't just take our word for it. Read through some of the thousands of reviews that our customers have taken the time to submit to the independent review websites.</p>
<div class="reviews-container">
<p class="bold title">Independent Review Websites</p>
<p class="bold"><a href="#">PriceGrabber</a> &mdash; 2,500+ Reviews</p>
<div class="review">
<div class="stars"><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /></div>
<p>"Super easy and Quick. Probably spent less than 5 minutes in your website. Great place!"</p>
</div>
<div class="review">
<div class="stars"><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-empty.png"}}" /></div>
<p>"I didn't know exactly which cover to select so I contacted customer service and they were very helpful."</p>
</div>
<div class="review">
<div class="stars"><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /></div>
<p>"Site was easy to navigate. Was able to find a cover to fit on a older model boat. Great products."</p>
</div>
<div class="review">
<div class="stars"><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /></div>
<p>"The website was well organized and easy to navigate. Quality product and fast shipping."</p>
</div>
<div class="review last">
<div class="stars"><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /><img src="{{view url="images/star-filled.png"}}" /></div>
<p>"Found boat cover with no trouble. Was guided through selection process with ease."</p>
</div>
</div>
HTML;

            $layoutUpdateXml = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;


            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'content_heading' => '',
                'layout_update_xml' => $layoutUpdateXml
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();


            $identifier = 'sale_banner_mobile';
            $title = 'Sale Banner Mobile';

            $content = <<<HTML
<div class="hero sale-banner-mobile">
<div class="row"><img src="{{view url="images/sale-banner-mobile.png"}}" /></div>
</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();

            $identifier = 'best_price_popup';
            $title = 'Best Price Popup';

            $content = <<<HTML
<p><img src="{{view url="images/best-price-img.png"}}" /></p>
<p><b>We are committed to giving our customers the highest quality products at the lowest possible price.</b> If you are ordering and find an identical product for a lower price, we will gladly match that price (excluding advertising specials and end-of-year closeouts).</p>
<p>Price match discounts may not be used with any other promotional offers and must be applied before any applicable taxes. In the event a pricing error occurs by the advertising competitor, the price match guarantee will not apply.</p>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();

            $identifier = 'custom_fit_popup';
            $title = 'Custom Fit Popup';

            $content = <<<HTML
<p><img src="{{view url="images/madeinusa-img.png"}}" /></p>
<p><b>We guarantee that all boat covers, pontoon covers, personal watercraft covers, and bimini tops purchased will fit the boat, pontoon, or personal watercraft it was ordered for.</b></p>
<p>If the covers you receive do not fit or if we accidentally send the incorrect product, contact us within 30 days of when you receive the covers and we will gladly exchange them for the correct size at no additional cost to the customer. If we cannot offer a replacement that fits, the customer will be issued a full refund including all original and return shipping costs.</p>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();

        }

        if (version_compare($context->getVersion(), '1.0.9') < 0) {
            $identifier = 'cover_world_footer_links';
            $title = 'Cover World Footer Links';

            $content = <<<HTML
<ul class="footer-links">
<li class="item-links-list first">
<p class="title">Cover Types</p>
<ul class="links-list">
<li class="item"><a href="/boat-covers.html">Boat Covers</a></li>
<li class="item"><a href="/pontoon-covers.html">Pontoon Covers</a></li>
<li class="item"><a href="/jet-ski-covers.html">Jet Ski Covers</a></li>
<li class="item"><a href="/bimini-tops.html">Bimini Tops</a></li>
<li class="item"><a href="/fishing-boat-covers.html">Fishing Boat Covers</a></li>
</ul>
</li>
<li class="item-links-list">
<p class="title">Policies</p>
<ul class="links-list">
<li class="item"><a href="/returns-exchanges">Return Policy</a></li>
<li class="item"><a href="#">Cancellation Policy</a></li>
<li class="item"><a href="#">Warranty Policy</a></li>
<li class="item"><a href="#">Shipping Policy</a></li>
<li class="item"><a href="#">Privacy Policy</a></li>
</ul>
</li>
<li class="item-links-list">
<p class="title">Help</p>
<ul class="links-list">
<li class="item"><a href="/faq">FAQs</a></li>
<li class="item"><a href="/cuustomer/account">My Account</a></li>
<li class="item"><a href="/contact">Contact Us</a></li>
<li class="item"><a href="#">Order Status</a></li>
<li class="item"><a href="#">Return Center</a></li>
</ul>
</li>
<li class="item-links-list last">
<p class="title">Our Promise</p>
<ul class="links-list">
<li class="item"><a href="#">Sure-Fit Guarantee</a></li>
<li class="item"><a href="#">Hassle-Free Returns</a></li>
<li class="item"><a href="#">Factory Direct Pricing</a></li>
<li class="item"><a href="#">Largest Online Inventory</a></li>
<li class="item"><a href="#">Safe &amp; Secure Shopping</a></li>
</ul>
</li>
</ul>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.10') < 0) {
            $identifier = 'category_left_column';
            $title = 'Category Left Column';

            $content = <<<HTML
<div class="banner"><img src="{{view url="images/made_in_usa.png"}}" /></div>
<div class="banner"><a id="best-price-banner" href="#"><img src="{{view url="images/best_price_banner.png"}}" /></a></div>
<script type="text/javascript" xml="space">// <![CDATA[
// 
require([
    'jquery'
], function ($) {
    $(function() {

    $("#best-price-banner").on('click',function(){
        $("#best-price-modal").modal("openModal");
    });

});
});
// 
// ]]></script>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.11') < 0) {
            $identifier = 'boat_covers_finder';
            $title = 'Boat Covers Finder';

            $content = <<<HTML
<div class="finder-top-banner boat-top-banner">
<div class="finder-container"><img class="start-here" src="{{view url="images/boat-covers/START_HERE.png"}}" />
<p class="title">Select Your Boat</p>
<p>{{block class="Amasty\Finder\Block\Form" block_id="finder_form" id="1"}}</p>
</div>
{{block class="Magento\Cms\Block\Block" block_id="why_shop"}}</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores' => array(0),
                'content' => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
            ////////////////////////////////
            $identifier = 'jet_ski_covers_finder';
            $title = 'Jet Ski Covers Finder';

            $content = <<<HTML
<div class="finder-top-banner jet-ski-top-banner">
<div class="finder-container"><img class="start-here" src="{{view url="images/boat-covers/START_HERE.png"}}" />
<p class="title">Select Your PWC</p>
<p>{{block class="Amasty\Finder\Block\Form" block_id="finder_form" id="2"}}</p>
</div>
{{block class="Magento\Cms\Block\Block" block_id="why_shop"}}</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores' => array(0),
                'content' => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
////////////////////////////////
            $identifier = 'jet_ski_covers_tabs';
            $title = 'Jet Ski Covers Tabs';

            $content = <<<HTML
<div class="info-tabs">
<div class="tab active first">
<p>The Importance of Jet Ski Covers</p>
</div>
<div class="content active">
<p>Personal watercrafts are enjoyable because of the freedom they allow. Everyone loves to zip around the lake or bay on their PWC, whether they’re alone or with a friend. Even kids love to ride on the back of these machines!</p>
<p>The biggest advantages of PWCs are their agility and mobility. You can maneuver a PWC easily and quickly, which also gives them added safety. PWCs can go nearly anywhere, and are easily transported either on the back of a truck or with a trailering hitch. And let’s not forget how much fun they are. Some PWCs are powerful enough to be used for watersports. Those that aren’t still offer plenty of enjoyment from riding in the open air.</p>
<p>Although PWCs are small, they can still be quite expensive. In order to keep yours running well for years to come, it should be cared for and maintained. A PWC cover is an excellent way to protect your watercraft from harsh outdoor elements that can damage the seat fabric, steering control and exterior paint. A PWC cover is a small purchase in comparison to the price of repairing or restoring any of these features.</p>
</p>Purchase a cover to give your PWC the protection it deserves!</p>
</div>
<div class="tab">
<p>Best-Price Guarantee</p>
</div>
<div class="content">
<p>Lorem ipsum BESTPRICE</p>
</div>
<div class="tab last">
<p>Custom-Fit</p>
</div>
<div class="content">
<p>Lorem ipsum CUSTOM Fit</p>
</div>
</div>
<script type="text/javascript" xml="space">// 
// 
// 
// 
require([
    'jquery'
], function ($) {
    $(function() {
 
    $('.tab').click(function() {
        $('.tab').removeClass('active');
        $('.content').removeClass('active');
        $(this).addClass('active');
        $(this).next().addClass('active');
 
    });
 
});
});
// 
// 
// </script>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores' => array(0),
                'content' => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();

        }

        if (version_compare($context->getVersion(), '1.0.12') < 0) {
            $identifier = 'jet_ski_whats_included';
            $title = 'Jet Ski What\'s Included';

            $content = <<<HTML
<div class="whats-included">
<div class="title">
<p>What's included</p>
</div>
<div class="main-image"><img src="{{view url="images/included-main-image.png"}}" /></div>
<ul class="bullets">
<li><span class="bullet">6 Tie-Down Straps</span><span class="free">Free</span></li>
<li><span class="bullet">Sewn in Tie-Down Loops</span><span class="free">Free</span></li>
<li><span class="bullet">¼” Shock Cord for Fit</span><span class="free">Free</span></li>
</ul>
</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores' => array(0),
                'content' => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.13') < 0) {
            $identifier = 'jet_ski_whats_included';
            $title = 'Jet Ski What\'s Included';

            $content = <<<HTML
<div class="whats-included">
<div class="title">
<p>What's included</p>
</div>
<div class="main-image"><img src="{{view url="images/included-main-image.png"}}" /></div>
<ul class="bullets">
<li>
<p class="bullet">Storage Pouch</p>
<span class="free">Free</span></li>
<li>
<p class="bullet">Sewn in Tie-Down Loops</p>
<span class="free">Free</span></li>
<li>
<p class="bullet">10-Year Warranty</p>
<span class="free">Free</span></li>
</ul>
</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores' => array(0),
                'content' => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.14') < 0) {
            $identifier = 'category_left_column';
            $title = 'Category Left Column';

            $content = <<<HTML
<div class="banner"><a id="made-in-usa-banner" href="#"><img src="{{view url="images/made_in_usa.png"}}" /></a></div>
<div class="banner"><a id="best-price-banner" href="#"><img src="{{view url="images/best_price_banner.png"}}" /></a></div>
<script type="text/javascript" xml="space">// <![CDATA[
// 
// 
// 
require([
    'jquery'
], function ($) {
    $(function() {

    $("#best-price-banner").on('click',function(){
        $("#best-price-modal").modal("openModal");
    });

$("#made-in-usa-banner").on('click',function(){
        $("#custom-fit-modal").modal("openModal");
    });

});
});
// 
// 
// 
// ]]></script>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.15') < 0) {
            $identifier = 'reviews_customer_quotes';
            $title = 'Reviews Customer Quotes';

            $content = <<<HTML
<p class="title">Customer Quotes</p>
<p class="quote">"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla porta tincidunt turpis blandit finibus. Vivamus lobortis, tortor at egestas vehicula."</p>
<p class="author">- Steve Smith</p>
<p class="quote">"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla porta tincidunt turpis blandit finibus. Vivamus lobortis, tortor at egestas vehicula."</p>
<p class="author">- Steve Smith</p>
<p class="quote">"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla porta tincidunt turpis blandit finibus. Vivamus lobortis, tortor at egestas vehicula."</p>
<p class="author">- Steve Smith</p>
<p class="quote">"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla porta tincidunt turpis blandit finibus. Vivamus lobortis, tortor at egestas vehicula."</p>
<p class="author">- Steve Smith</p>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }


        if (version_compare($context->getVersion(), '1.0.16') < 0) {
            $identifier = 'shoppingcart_whatsthis_popup';
            $title = 'Shopping Cart What\'s This Popup';

            $content = <<<HTML
Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }
        if (version_compare($context->getVersion(), '1.0.17') < 0) {
            $identifier = 'product_shipping';
            $title = 'Product Shipping';

            $content = <<<HTML
<div class="iner-vlooo">
<div class="leftblock">
<h2>Shipping</h2>
<p>Ground shipping is free for all orders over $100 shipped within the contiguous United States. However, due to the high cost of shipping to Hawaii, Alaska, and US Territories, an additional shipping and handling cost is applied to these areas. This charge varies depending on where the package is being shipped and the exact cost is calculated and displayed during the checkout process before an order is completed. Overnight, 2 Day, and 3 Day shipping options are not eligible for free shipping.</p>
<p>Orders are generally processed within 24 hours and shipped within 48-72 hours from the time payment is received. Orders placed on weekends or after 3pm EST will be processed on the next business day. Customers will receive an order confirmation email that includes a summary of their order, along with the shipping address for the order. If an error is found in the shipping address, please contact us at (888) 619-6852 as soon as possible and our customer care team will do their best to update your order with the correct shipping information or intercept the package and correct the destination if it has already shipped.</p>
<h2>Shipping Internationally</h2>
<p>We will gladly ship your product to Canada; however, these orders are not eligible for free shipping and may be subject to customs fees, taxes, or duties. These fees are not determined until the product crosses the border; therefore, these costs are the responsibility of the customer. Our shipping and handling cost does not include any of these fees.</p>
</div>
<div class="rightblockshiping"><img src="{{view url="images/shipping.png"}}" /></div>
</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.18') < 0) {
            $identifier = 'product_cleaning';
            $title = 'Product Cleaning';

            $content = <<<HTML
<div class="iner-vlooo">
<div class="leftblock">
<h2>Cleaning</h2>
<p>All of our covers are easy to wash. For a light hand wash, brush off any loose dirt and spray with a water hose. Dilute a mild liquid detergent, like Woolite, in cold to lukewarm water and lightly cleanse your cover with a sponge or gentle brush.  Allow cleaning solution to soak into the fabric.  Finally, rinse thoroughly until all soap residue is removed.  Allow your cover to air dry. <strong>NEVER</strong> place a cover in an automatic dryer.</p>
<p>For a deeper clean, such as to remove stubborn stains or mildew, mix eight ounces (1 cup) of chlorine bleach with two ounces of mild detergent, like Woolite, with one gallon of water.  Clean the stain with a soft bristle brush and allow the mixture to soak into the fabric for up to 15 minutes, then rinse thoroughly until all detergent residue is removed.  Allow your cover to air dry and repeat if necessary.</p>
<p>These are recommendations and all cleaning products and methods should be tested on an inconspicuous spot before using it on the original stain/soiling.</p>
</div>
<div class="rightblockshiping"><img src="{{view url="images/cleaning.png"}}" /></div>
</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.19') < 0) {
            $identifier = 'product_installation';
            $title = 'Product Installation';

            $content = <<<HTML
<div class="iner-vlooo">
<div class="leftblock">
<h2>Installation</h2>
<p>Installing your new PWC cover is simple.  To begin, we recommend identifying any sharp areas on your PWC that will be in contact with the cover.  Padding of some sort, or duct tape, can be applied to the cover in these areas to protect your cover. Next, identify the tapered, or front end of your cover.  Drape the front end over the front of your PWC.  Pull the cover below the rub rail around your entire PWC.   Adjust cover fit by pulling cover tight to eliminate any sagging areas.</p>
<p>Only covers that have been warranted for travel should be trailered.  When trailering, heavy-duty tie-down straps should be used.  Be sure that your cover has been tightly secured and there are no loose areas that can flap in the wind.  After driving one mile, recheck all straps and adjust as necessary.</p>
<h2>Removal</h2>
<p>Remove the PWC cover by folding to the center from both sides and rolling the cover from back to front.  This will make it easier to repeat steps 1 through 5 when covering your PWC again.</p>
<p>If you use the same procedure each time, you will find it only takes about a minute to install or to remove the cover.</p>
</div>
<div class="rightblockshiping"><img src="{{view url="images/Installation1.png"}}" /><img src="{{view url="images/Installation2.png"}}" /></div>
</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.20') < 0) {
            $identifier = 'product_warranty';
            $title = 'Product Warranty';

            $content = <<<HTML
<div class="iner-vlooo">
<div class="leftblock">
<h2>Warranty</h2>
<p>The craftsmanship of your product is guaranteed to be free of defects. The manufacturer warrants to the purchaser that they will correct any defects in material or workmanship by either repair or replacement within 3 years of the original purchase date.  Select Covers is pleased to extend this warranty period by an additional 4 years providing you with an incredible 7-year warranty.</p>
<p>The warranty does not cover normal wear, weather soiling, acts of nature, or stains from dirt or pollutants. The cover must be trailered according to specific manufacturer instructions.  Failure to follow specific manufacturer trailering instructions will void the warranty. The cover must be properly utilized and maintained from the time of purchase.</p>
<ul><li>Do not allow water, snow or ice to pool on cover.</li><li>Do not place a wet cover in storage.</li></ul>
<p>This warranty does not cover damage to cover or personal watercraft in any form from use of this cover including but not limited to the following acts: misuse, abuse, neglect, improper personal watercraft protection, accidents or acts of God.  Additional items not covered include damage as a result of animals such as rodents, birds, pets, insects, etc. Product is also not covered if any damage is incurred as a result of coming into contact with sharp objects that would result in punctures or tears.</p>
<p>To initiate a claim for a product still covered under the manufacturer's or Select Covers extended warranty, please carefully review our warranty policy and then complete the form located on our warranty claim page. After your claim is submitted, a customer support specialist will review your request and respond with instructions for returning the defective cover to us for repair or replacement, pending an inspection.</p>
<p>Based on damage, the manufacturer will repair or replace the product at their discretion. The manufacturer will cover the cost of shipping the repaired cover or replacement cover back to you.</p>
</div>
<div class="rightblockshiping"><img src="{{view url="images/warranty1.png"}}" /><img src="{{view url="images/warranty2.png"}}" /><img src="{{view url="images/warranty3.png"}}" /></div>
</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores'    => array(0),
                'content'   => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.21') < 0) {
            $setup->getConnection()->query("UPDATE cms_page SET content_heading = '' WHERE  page_id =1");
        }

        if (version_compare($context->getVersion(), '1.0.22') < 0) {
            $identifier = 'saveupto';
            $title = 'Save up to';

            $content = <<<HTML
Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores' => array(0),
                'content' => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }
        if (version_compare($context->getVersion(), '1.0.23') < 0) {
            $identifier = 'newlettersuccess';
            $title = 'Newletter Success';

            $content = <<<HTML
    <h1>Thank you</h1>
    <div class="mark-container">
        <img src="{{view url="images/success-mark.png"}}" />
    </div>
    <p>Thank you for your subscription.</p>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores' => array(0),
                'content' => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.24') < 0) {
            $identifier = 'category_left_column';
            $title = 'Category Left Column';

            $content = <<<HTML
                        <div class="banner"><a id="made-in-usa-banner" href="javascript:void(0)"><img src="{{view url="images/made_in_usa.png"}}" /></a></div>
                        <div class="banner"><a id="best-price-banner" href="javascript:void(0)"><img src="{{view url="images/best_price_banner.png"}}" /></a></div>
                        <script type="text/javascript" xml="space">// <![CDATA[
                        // 
                        require([
                            'jquery'
                        ], function ($) {
                            $(function() {

                            $("#best-price-banner").on('click',function(){
                                $("#best-price-modal").modal("openModal");
                            });

                        $("#made-in-usa-banner").on('click',function(){
                                $("#custom-fit-modal").modal("openModal");
                            });

                        });
                        });
                        // 
                        // ]]></script>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores' => array(0),
                'content' => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.25') < 0) {
            $identifier = 'product_review';
            $title = 'Product Review';

            $content = <<<HTML
<div class="block-content">

        <div class="top-bar">
            <span class="review-count">Reviewed by <strong>5</strong> Customer(s)</span>
        </div>

        <ol class="items review-items">
                    <li class="item review-item" itemscope="" itemprop="review" itemtype="http://schema.org/Review">

                                    <div class="review-ratings">
                                                    <div class="rating-summary item" itemprop="reviewRating" itemscope="" itemtype="http://schema.org/Rating">
                                <div class="rating-box">
                                    <div class="rating" style="width:100%"></div>
                                </div>
                            </div>
                                                <p class="review-author">
                            <span class="review-details-label">by</span>
                            <strong class="review-details-value" itemprop="author">Lorem Ipsum</strong>
                        </p>
                    </div>
                

                <div class="review-content">
                    <div class="review-title" itemprop="name">Lorem Ipsum</div>

                    <div class="review-description" itemprop="description">
                        In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments                    </div>
                </div>
            </li>
                    <li class="item review-item" itemscope="" itemprop="review" itemtype="http://schema.org/Review">

                                    <div class="review-ratings">
                                                    <div class="rating-summary item" itemprop="reviewRating" itemscope="" itemtype="http://schema.org/Rating">
                                <div class="rating-box">
                                    <div class="rating" style="width:100%"></div>
                                </div>
                            </div>
                                                <p class="review-author">
                            <span class="review-details-label">by</span>
                            <strong class="review-details-value" itemprop="author">Lorem Ipsum</strong>
                        </p>
                    </div>
                

                <div class="review-content">
                    <div class="review-title" itemprop="name">Lorem Ipsum</div>

                    <div class="review-description" itemprop="description">
                        In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments                    </div>
                </div>
            </li>
                    <li class="item review-item" itemscope="" itemprop="review" itemtype="http://schema.org/Review">

                                    <div class="review-ratings">
                                                    <div class="rating-summary item" itemprop="reviewRating" itemscope="" itemtype="http://schema.org/Rating">
                                <div class="rating-box">
                                    <div class="rating" style="width:100%"></div>
                                </div>
                            </div>
                                                <p class="review-author">
                            <span class="review-details-label">by</span>
                            <strong class="review-details-value" itemprop="author">lorem ipsum</strong>
                        </p>
                    </div>
                

                <div class="review-content">
                    <div class="review-title" itemprop="name">lorem ipsum</div>

                    <div class="review-description" itemprop="description">
                        In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments                    </div>
                </div>
            </li>
                    <li class="item review-item" itemscope="" itemprop="review" itemtype="http://schema.org/Review">

                                    <div class="review-ratings">
                                                    <div class="rating-summary item" itemprop="reviewRating" itemscope="" itemtype="http://schema.org/Rating">
                                <div class="rating-box">
                                    <div class="rating" style="width:100%"></div>
                                </div>
                            </div>
                                                <p class="review-author">
                            <span class="review-details-label">by</span>
                            <strong class="review-details-value" itemprop="author">Lorem Ipsum</strong>
                        </p>
                    </div>
                

                <div class="review-content">
                    <div class="review-title" itemprop="name">Lorem Ipsum</div>

                    <div class="review-description" itemprop="description">
                        In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments                    </div>
                </div>
            </li>
                    <li class="item review-item" itemscope="" itemprop="review" itemtype="http://schema.org/Review">

                                    <div class="review-ratings">
                                                    <div class="rating-summary item" itemprop="reviewRating" itemscope="" itemtype="http://schema.org/Rating">
                                <div class="rating-box">
                                    <div class="rating" style="width:100%"></div>
                                </div>
                            </div>
                                                <p class="review-author">
                            <span class="review-details-label">by</span>
                            <strong class="review-details-value" itemprop="author">Lorem Ipsum</strong>
                        </p>
                    </div>
                

                <div class="review-content">
                    <div class="review-title" itemprop="name">Lorem Ipsum</div>

                    <div class="review-description" itemprop="description">
                        In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments                    </div>
                </div>
            </li>
                </ol>
    </div>
</div>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores' => array(0),
                'content' => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }


        if (version_compare($context->getVersion(), '1.0.26') < 0) {
            $identifier = 'return_policy';
            $title = 'Return Policy';

            $content = <<<HTML
<div class="title">
<h1>Return Policy</h1>
</div>
<p>In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments</p>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'mageworx_hreflang_identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        if (version_compare($context->getVersion(), '1.0.27') < 0) {
            $identifier = 'cancellation_policy';
            $title = 'Cancellation Policy';

            $content = <<<HTML
<div class="title">
<h1>Cancellation Policy</h1>
</div>
<p>In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments</p>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'mageworx_hreflang_identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

if (version_compare($context->getVersion(), '1.0.28') < 0) {
            $identifier = 'warranty_policy';
            $title = 'Warranty Policy';

            $content = <<<HTML
<div class="title">
<h1>Warranty Policy</h1>
</div>
<p>In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments</p>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'mageworx_hreflang_identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

if (version_compare($context->getVersion(), '1.0.29') < 0) {
            $identifier = 'shipping_policy';
            $title = 'Shipping Policy';

            $content = <<<HTML
<div class="title">
<h1>Shipping Policy</h1>
</div>
<p>In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments</p>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'mageworx_hreflang_identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        if (version_compare($context->getVersion(), '1.0.30') < 0) {
            $identifier = 'privacy_policy';
            $title = 'Privacy Policy';

            $content = <<<HTML
<div class="title">
<h1>Privacy Policy</h1>
</div>
<p>In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments</p>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'mageworx_hreflang_identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        if (version_compare($context->getVersion(), '1.0.31') < 0) {
            $identifier = 'return_center';
            $title = 'Return Center';

            $content = <<<HTML
<div class="title">
<h1>Return Center</h1>
</div>
<p>In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments</p>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'mageworx_hreflang_identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        if (version_compare($context->getVersion(), '1.0.32') < 0) {
            $identifier = 'sure_fit_guarantee';
            $title = 'Sure-Fit Guarantee';

            $content = <<<HTML
<div class="title">
<h1>Sure-Fit Guarantee</h1>
</div>
<p>In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments</p>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'mageworx_hreflang_identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        if (version_compare($context->getVersion(), '1.0.33') < 0) {
            $identifier = 'hassle_free_returns';
            $title = 'Hassle-Free Returns';

            $content = <<<HTML
<div class="title">
<h1>Hassle-Free Returns</h1>
</div>
<p>In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments</p>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'mageworx_hreflang_identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        if (version_compare($context->getVersion(), '1.0.34') < 0) {
            $identifier = 'factory_direct_pricing';
            $title = 'Factory Direct Pricing';

            $content = <<<HTML
<div class="title">
<h1>Factory Direct Pricing</h1>
</div>
<p>In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments</p>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'mageworx_hreflang_identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        if (version_compare($context->getVersion(), '1.0.35') < 0) {
            $identifier = 'largest_online_inventory';
            $title = 'Largest Online Inventory';

            $content = <<<HTML
<div class="title">
<h1>Largest Online Inventory</h1>
</div>
<p>In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments</p>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'mageworx_hreflang_identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        if (version_compare($context->getVersion(), '1.0.36') < 0) {
            $identifier = 'safe_secure_shopping';
            $title = 'Safe &amp; Secure Shopping';

            $content = <<<HTML
<div class="title">
<h1>Safe &amp; Secure Shopping</h1>
</div>
<p>In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments</p>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'mageworx_hreflang_identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        if (version_compare($context->getVersion(), '1.0.37') < 0) {
            $identifier = 'terms_of_use';
            $title = 'Terms of Use';

            $content = <<<HTML
<div class="title">
<h1>Terms of Use</h1>
</div>
<p>In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments</p>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'mageworx_hreflang_identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }
        if (version_compare($context->getVersion(), '1.0.38') < 0) {
            $identifier = 'cover_world_footer_links';
            $title = 'Cover World Footer Links';

            $content = <<<HTML
                        <ul class="footer-links">
<li class="item-links-list first">
<p class="title">Cover Types</p>
<ul class="links-list">
<li class="item"><a href="{{store direct_url="Boat Covers"}}"></a></li>
<li class="item"><a href="{{store direct_url="pontoon-covers.html"}}">Pontoon Covers</a></li>
<li class="item"><a href="{{store direct_url="jet-ski-covers.html"}}">Jet Ski Covers</a></li>
<li class="item"><a href="{{store direct_url="bimini-tops.html"}}">Bimini Tops</a></li>
<li class="item"><a href="{{store direct_url="fishing-boat-covers.html"}}">Fishing Boat Covers</a></li>
</ul>
</li>
<li class="item-links-list">
<p class="title">Policies</p>
<ul class="links-list">
<li class="item"><a href="{{store direct_url="return_policy"}}">Return Policy</a></li>
<li class="item"><a href="{{store direct_url="cancellation_policy"}}">Cancellation Policy</a></li>
<li class="item"><a href="{{store direct_url="warranty_policy"}}">Warranty Policy</a></li>
<li class="item"><a href="{{store direct_url="shipping_policy"}}">Shipping Policy</a></li>
<li class="item"><a href="{{store direct_url="privacy_policy"}}">Privacy Policy</a></li>
</ul>
</li>
<li class="item-links-list">
<p class="title">Help</p>
<ul class="links-list">
<li class="item"><a href="{{store direct_url="faq"}}">FAQs</a></li>
<li class="item"><a href="{{store direct_url="customer/account"}}">My Account</a></li>
<li class="item"><a href="{{store direct_url="contact"}}">Contact Us</a></li>
<li class="item"><a href="{{store direct_url="sales/guest/form/"}}">Order Status</a></li>
<li class="item"><a href="{{store direct_url="return_center"}}">Return Center</a></li>
</ul>
</li>
<li class="item-links-list last">
<p class="title">Our Promise</p>
<ul class="links-list">
<li class="item"><a href="{{store direct_url="sure_fit_guarantee"}}">Sure-Fit Guarantee</a></li>
<li class="item"><a href="{{store direct_url="hassle_free_returns"}}">Hassle-Free Returns</a></li>
<li class="item"><a href="{{store direct_url="factory_direct_pricing"}}">Factory Direct Pricing</a></li>
<li class="item"><a href="{{store direct_url="largest_online_inventory"}}">Largest Online Inventory</a></li>
<li class="item"><a href="{{store direct_url="safe_secure_shopping"}}">Safe &amp; Secure Shopping</a></li>
</ul>
</li>
</ul>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'is_active' => true,
                'stores' => array(0),
                'content' => $content
            );
            $block = $this->_blockFactory->create();
            $block->getResource()->load($block, $identifier);
            if (!$block->getData()) {
                $block->setData($data);
            } else {
                $block->addData($data);
            }
            $block->save();
        }

        if (version_compare($context->getVersion(), '1.0.39') < 0) {
            $identifier = 'security_policy';
            $title = 'Security Policy';

            $content = <<<HTML
<div class="title">
<h1>Security Policy</h1>
</div>
<p>In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments</p>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'mageworx_hreflang_identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }
        if (version_compare($context->getVersion(), '1.0.40') < 0) {
            $identifier = 'about_us';
            $title = 'About Us';

            $content = <<<HTML
<div class="title">
<h1>About Us</h1>
</div>
<p>In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements. Besides, random text risks to be unintendedly humorous or offensive, an unacceptable risk in corporate environments</p>
HTML;

            $layoutUpdates = <<<HTML
<referenceContainer name="sidebar.main">
            <block class="Magento\Framework\View\Element\Template" name="cms.left.links" template="cms/left_links.phtml"/>
        </referenceContainer>
HTML;

            $data = array(
                'title' => $title,
                'identifier' => $identifier,
                'mageworx_hreflang_identifier' => $identifier,
                'is_active' => true,
                'page_layout' => '2columns-left',
                'stores'    => array(0),
                'content'   => $content,
                'layout_update_xml' => $layoutUpdates
            );
            $page = $this->_pageFactory->create();
            $page->getResource()->load($page, $identifier);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->save();
        }

        $setup->endSetup();
    }
}
<?php
namespace GreenHonchos\CustomCanonical\Plugin;

use Magento\Framework\View\Page\Config;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;

class ModifyCanonicalUrl
{
    protected $urlBuilder;
    protected $storeManager;
    protected $request;

    public function __construct(
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    public function beforeAddRemotePageAsset(Config $subject, $url, $type, $attributes = [])
    {
        // Check if the asset type is a canonical link
        if ($type === 'canonical') {
            // Get the current page canonical URL
            $canonicalUrl = $this->urlBuilder->getCurrentUrl();

            // Get the base URL of the store (home page URL)
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            
            // Define the live domain you want to use
            $liveDomain = 'https://forevernew.co.in';

            // Check if it's the home page or a CMS page
            if ($canonicalUrl == $baseUrl || $this->isCmsPage()) {
                // If it's the home page or a CMS page, set the canonical to the live domain root
                $updatedCanonicalUrl = $liveDomain;
            } else {
                // For other pages, replace the current domain with the live domain
                $updatedCanonicalUrl = preg_replace("/^https?:\/\/[^\/]+/", $liveDomain, $canonicalUrl);
            }

            // Return the modified canonical URL
            return [$updatedCanonicalUrl, $type, $attributes];
        }

        // If it's not a canonical link, return the original URL
        return [$url, $type, $attributes];
    }

    // Check if the current request is for a CMS page
    protected function isCmsPage()
    {
        $controllerName = $this->request->getControllerName();
        return $controllerName === 'index' && $this->request->getFullActionName() === 'cms_index_index';
    }
}

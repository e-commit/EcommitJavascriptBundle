<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) Hubert LECORCHE <hlecorche@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Listener;

use Ecommit\JavascriptBundle\jQuery\Manager;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class JavascriptListener 
{
    protected $jQueryManager;

    /**
     * Constructor
     * 
     * @param Manager $jQueryManager 
     */
    public function __construct(Manager $jQueryManager)
    {
        $this->jQueryManager = $jQueryManager;
    }

    
    /**
     * Modifies the response to apply HTTP expiration header fields.
     *
     * @param FilterResponseEvent $event The notified event
     */
    public function onCoreResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType())
        {
            return;
        }
        
        $response = $event->getResponse();
        $content = $response->getContent();
        
        $content_js = $this->jQueryManager->getCodeInsertJs();
        $content_css = $this->jQueryManager->getCodeInsertCss();
        $tag_js = $this->jQueryManager->getJsTag();
        $tag_css = $this->jQueryManager->getCssTag();
        
        $content = str_replace($tag_js, $content_js, $content);
        $content = str_replace($tag_css, $content_css, $content);
        
        $response->setContent($content);
    }
}

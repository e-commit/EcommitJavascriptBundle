<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) Hubert LECORCHE <hlecorche@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Helper\Twig;

use Twig_Extension;
use Twig_Function_Method;
use Ecommit\JavascriptBundle\jQuery\Manager;

class jQueryExtension extends Twig_Extension
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
    * Returns the name of the extension.
    *
    * @return string The extension name
    */
    public function getName()
    {
        return 'ecommit_jQuery';
    }
    
    /**
    * Returns a list of global functions to add to the existing list.
    *
    * @return array An array of global functions
    */
    public function getFunctions()
    {
        return array(
            'ecommit_jQuery_insert_js' => new Twig_Function_Method($this, 'insert_js', array('is_safe' => array('all'))),
            'ecommit_jQuery_insert_css' => new Twig_Function_Method($this, 'insert_css', array('is_safe' => array('all'))),
            'ecommit_jQuery_ajax' => new Twig_Function_Method($this, 'jQuery_remote_function', array('is_safe' => array('all'))),
            'ecommit_jQuery_ajax_link' => new Twig_Function_Method($this, 'jQuery_link_to_remote', array('is_safe' => array('all'))),
            'ecommit_jQuery_ajax_button' => new Twig_Function_Method($this, 'jQuery_button_to_remote', array('is_safe' => array('all'))),
            'ecommit_jQuery_ajax_form' => new Twig_Function_Method($this, 'jQuery_form_to_remote', array('is_safe' => array('all'))),
        );
    }
    
    
    /**
     * Returns auto Js Tag
     * 
     * @see Manager::getJsTag
     * @return string 
     */
    public function insert_js()
    {
        return $this->jQueryManager->getJsTag();
    }
    
    
    /**
     * Returns auto Css Tag
     * 
     * @see Manager::getCssTag
     * @return type 
     */
    public function insert_css()
    {
        return $this->jQueryManager->getCssTag();
    }
    
    /**
     * Returns the javascript needed for a remote function defined by 'url'
     * 
     * @param string $url Request Url
     * @param array $options Options. See Manager::jQueryRemoteFunction
     * @see Manager:jQueryRemoteFunction
     * @return string 
     */
    public function jQuery_remote_function($url, $options = array())
    {
        return $this->jQueryManager->jQueryRemoteFunction($url, $options);
    }
    
    /**
     * Returns a link to a remote action defined by 'url'
     * 
     * @param string $name  The link text
     * @param string $url  The link url
     * @param array $options Options. See Manager::jQueryLinkToRemote
     * @param array $html_options Html options
     * @see Manager::jQueryLinkToRemote
     * @return string 
     */
    public function jQuery_link_to_remote($name, $url, $options = array(), $html_options = array())
    {
        return $this->jQueryManager->jQueryLinkToRemote($name, $url, $options, $html_options);
    }
    
    /**
     * Returns an html button to a remote action defined by 'url'
     * 
     * @param string $name  The button text
     * @param string $url  The button url
     * @param array $options Options. See Manager::jQueryButtonToRemote
     * @param array $html_options Html options
     * @see Manager::jQueryButtonToRemote
     * @return string 
     */
    public function jQuery_button_to_remote($name, $url, $options = array(), $html_options = array())
    {
        return $this->jQueryManager->jQueryButtonToRemote($name, $url, $options, $html_options);
    }
    
    /**
     * Returns a form tag that will submit using XMLHttpRequest in the background instead of the regular 
     * reloading POST arrangement.
     * 
     * @param string $name  The button text
     * @param array $options Options. See Manager::jQueryFormToRemote
     * @param array $html_options Html options
     * @see See Manager::jQueryFormToRemote
     * @return string 
     */
    public function jQuery_form_to_remote($url, $options = array(), $html_options = array())
    {
        return $this->jQueryManager->jQueryFormToRemote($url, $options, $html_options);
    }
}


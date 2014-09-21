<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Twig;

use Ecommit\JavascriptBundle\Helper\JqueryHelper;
use Twig_Extension;
use Twig_SimpleFunction;

class JqueryExtension extends Twig_Extension
{
    protected $jqueryHelper;


    /**
     * Constructor
     *
     * @param JqueryHelper $jqueryHelper
     */
    public function __construct(JqueryHelper $jqueryHelper)
    {
        $this->jqueryHelper = $jqueryHelper;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'ecommit_javascript_jquery_extension';
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction(
                'ecommit_javascript_jquery_ajax',
                array($this, 'jqueryAjax'),
                array('is_safe' => array('all'))
            ),
            new Twig_SimpleFunction(
                'ecommit_javascript_jquery_ajax_link',
                array($this, 'jqueryAjaxLink'),
                array('is_safe' => array('all'))
            ),
            new Twig_SimpleFunction(
                'ecommit_javascript_jquery_ajax_button',
                array($this, 'jqueryAjaxButton'),
                array('is_safe' => array('all'))
            ),
            new Twig_SimpleFunction(
                'ecommit_javascript_jquery_ajax_form',
                array($this, 'jqueryAjaxForm'),
                array('is_safe' => array('all'))
            ),
        );
    }

    /**
     * Returns the javascript needed for a remote function defined by 'url'
     *
     * @param string $url Request Url
     * @param array $options Options. See Manager::jQueryRemoteFunction
     * @see Manager:jQueryRemoteFunction
     * @return string
     */
    public function jqueryAjax($url, $options = array())
    {
        return $this->jqueryHelper->jQueryRemoteFunction($url, $options);
    }

    /**
     * Returns a link to a remote action defined by 'url'
     *
     * @param string $name The link text
     * @param string $url The link url
     * @param array $options Options. See Manager::jQueryLinkToRemote
     * @param array $htmlOptions Html options
     * @see Manager::jQueryLinkToRemote
     * @return string
     */
    public function jqueryAjaxLink($name, $url, $options = array(), $htmlOptions = array())
    {
        return $this->jqueryHelper->jQueryLinkToRemote($name, $url, $options, $htmlOptions);
    }

    /**
     * Returns an html button to a remote action defined by 'url'
     *
     * @param string $name The button text
     * @param string $url The button url
     * @param array $options Options. See Manager::jQueryButtonToRemote
     * @param array $htmlOptions Html options
     * @see Manager::jQueryButtonToRemote
     * @return string
     */
    public function jqueryAjaxButton($name, $url, $options = array(), $htmlOptions = array())
    {
        return $this->jqueryHelper->jQueryButtonToRemote($name, $url, $options, $htmlOptions);
    }

    /**
     * Returns a form tag that will submit using XMLHttpRequest in the background instead of the regular
     * reloading POST arrangement.
     *
     * @param string $name The button text
     * @param array $options Options. See Manager::jQueryFormToRemote
     * @param array $htmlOptions Html options
     * @see See Manager::jQueryFormToRemote
     * @return string
     */
    public function jqueryAjaxForm($url, $options = array(), $htmlOptions = array())
    {
        return $this->jqueryHelper->jQueryFormToRemote($url, $options, $htmlOptions);
    }
}

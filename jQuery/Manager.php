<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\jQuery;

use Ecommit\UtilBundle\Helper\UtilHelper;

class Manager 
{

    /**
     * @var UtilHelper
     */
    protected $util;

    /**
     * Constructor
     * @param UtilHelper $utilHelper
     */
    public function __construct(UtilHelper $util)
    {
        $this->util = $util;
    }

    
    /**
     * Returns a link to a remote action defined by 'url'
     * 
     * @param string $name  The link text
     * @param string $url  The link url
     * @param array $options Options. See below
     * @param array $html_options Html options
     * @return string 
     * 
     * Available options:
     * - update: The result of that request can then be inserted into a DOM object whose id can be specified with 'update'
     *   You can also specify a hash for 'update' to allow for easy redirection of output to an other DOM element if 
     *   a server-side error occurs  (success and failure)
     * - position: Optionally, you can use the 'position' parameter to influence how the target DOM element is updated. 
     *   It must be one of 'before', 'top', 'bottom', or 'after'. By default, it replaces the DOM element's content.
     * - loading: Loading callback
     * - complete: Complete callback
     * - success: Success callback
     * - script: Executes (or not) the code of the result (True / False)
     * - method: Method (POST / GET) (Default: POST)
     * - type: Synchronous or not (synchronous / false) (Default: false)
     * - with: Addionnels parameters in request
     * - before: Called before request is initiated
     * - after: Called immediately after request was initiated and before 'loading'
     * - condition: Perform remote request conditionally by this expression. Use this to describe browser-side conditions 
     *   when request should not be initiated
     * - confirm: Adds confirmation dialog
     * - cache: Request Cache (Default: false)
     * - crsf: [Not yet implemented]
     */
    public function jQueryLinkToRemote($name, $url, $options = array(), $html_options = array())
    {
        $html_options['href'] = isset($html_options['href']) ? $html_options['href'] : '#';
        $html_options['onclick'] = $this->jQueryRemoteFunction($url, $options).'; return false;';
        return $this->util->tag('a', $html_options, $name);
    }
    
    /**
     * Returns an html button to a remote action defined by 'url'
     * 
     * @param string $name  The button text
     * @param string $url  The button url
     * @param array $options Options. See Manager::jQueryLinkToRemote
     * @param array $html_options Html options
     * @see Manager::jQueryLinkToRemote
     * @return string  
     */
    public function jQueryButtonToRemote($name, $url, $options = array(), $html_options = array())
    {
        $html_options['type'] = 'button';
        $html_options['onclick'] = $this->jQueryRemoteFunction($url, $options).'; return false;';
        return $this->util->tag('button', $html_options, $name);
    }
    
    /**
     * Returns a form tag that will submit using XMLHttpRequest in the background instead of the regular 
     * reloading POST arrangement.
     * 
     * @param string $name  The button text
     * @param array $options Options. See Manager::jQueryLinkToRemote
     * @param array $html_options Html options
     * @see See Manager::jQueryLinkToRemote
     * @return string  
     */
    public function jQueryFormToRemote($url, $options = array(), $html_options = array())
    {
        $options['form'] = true;
        $html_options['action'] = isset($options_html['action']) ? $options_html['action'] : $url;
        $html_options['method'] = isset($options_html['method']) ? $options_html['method'] : 'post';
        $html_options['onsubmit'] = $this->jQueryRemoteFunction($url, $options).'; return false;';
        return $this->util->tag('form', $html_options, null, true);
    }
    
    /**
     * Returns the javascript needed for a remote function.
     * 
     * @param string $url Request Url
     * @param array $options Options. See Manager::jQueryLinkToRemote
     * @see Manager:jQueryLinkToRemote
     * @return string 
     */
    public function jQueryRemoteFunction($url, $options)
    {
        // Defining elements to update
    if (isset($options['update']) && is_array($options['update']))
    {
        // On success, update the element with returned data
        if (isset($options['update']['success'])) $update_success = "#".$options['update']['success'];

        // On failure, execute a client-side function
        if (isset($options['update']['failure'])) $update_failure = $options['update']['failure'];
    }
    else if (isset($options['update'])) $update_success = "#".$options['update'];

    // Update method
    $positionUpdate = isset($options['position']) ? $options['position'] : '';
        $updateMethod = 'html';
    switch ($positionUpdate) {
        case 'before':$updateMethod='before';break;
        case 'after':$updateMethod='after';break;
        case 'top':$updateMethod='prepend';break;
        case 'bottom':$updateMethod='append';break;
    }

    // Callbacks
    if (isset($options['loading'])) $callback_loading = $options['loading'];
    if (isset($options['complete'])) $callback_complete = $options['complete'];
    if (isset($options['success'])) $callback_success = $options['success'];
    
    $execute = 'false';
    if ((isset($options['script'])) && ($options['script'] == '1')) $execute = 'true';

    // Data Type
    if (isset($options['dataType']))
    {
        $dataType = $options['dataType'];
    }
    elseif ($execute)
    {
        $dataType = 'html';
    }
    else
    {
        $dataType = 'text';
    }

    // POST or GET ?
    $method = 'POST';
    if ((isset($options['method'])) && (strtoupper($options['method']) == 'GET')) $method = $options['method'];

    // async or sync, async is default
    if ((isset($options['type'])) && ($options['type'] == 'synchronous')) $type = 'false';

    // Is it a form submitting
    if (isset($options['form'])) $formData = 'jQuery(this).serialize()';
    elseif (isset($options['submit'])) $formData = '{\'#'.$options['submit'].'\'}.serialize()';
    // boutell and JoeZ99: 'with' should not be quoted, it's not useful
    // that way, see the Symfony documentation for the original remote_function
    elseif (isset($options['with'])) $formData = $options['with'];
    
    // Is it a link with csrf protection
    elseif(isset($options['csrf']) && $options['csrf'] == '1')
    {
            /*
             * TODO
             */
    }
    
    //Cache
    $cache = (empty($options['cache']))? 'false' : 'true';

    // build the function
    $function = "jQuery.ajax({";
    $function .= 'type:\''.$method.'\'';
    $function .= ',dataType:\'' . $dataType . '\'';
    $function .= ',cache: '.$cache;
    if (isset($type)) $function .= ',async:'.$type;
    if (isset($formData)) $function .= ',data:'.$formData;
    if (isset($update_success) && !isset($callback_success)) $function .= ',success:function(data, textStatus){jQuery(\''.$update_success.'\').'.$updateMethod.'(data);}';
    if (isset($update_failure)) $function .= ',error:function(XMLHttpRequest, textStatus, errorThrown){'.$update_failure.'}';
    if (isset($callback_loading)) $function .= ',beforeSend:function(XMLHttpRequest){'.$callback_loading.'}';
    if (isset($callback_complete)) $function .= ',complete:function(XMLHttpRequest, textStatus){'.$callback_complete.'}';
    if (isset($callback_success)) $function .= ',success:function(data, textStatus){'.$callback_success.'}';
        $function .= ',url:\''.$url.'\'';
    $function .= '})';

    if (isset($options['before']))
    {
        $function = $options['before'].'; '.$function;
    }
    if (isset($options['after']))
    {
        $function = $function.'; '.$options['after'];
    }
    if (isset($options['condition']))
    {
        $function = 'if ('.$options['condition'].') { '.$function.'; }';
    }
    if (isset($options['confirm']))
    {
        $function = "if (confirm('".$this->util->escape_javascript($options['confirm'])."')) { $function; }";
        if (isset($options['cancel']))
        {
            $function = $function.' else { '.$options['cancel'].' }';
        }
    }

    return $function;
    }
}

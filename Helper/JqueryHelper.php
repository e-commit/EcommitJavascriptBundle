<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Helper;

use Ecommit\UtilBundle\Helper\UtilHelper;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JqueryHelper
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
     * Returns a link to remote action defined by 'url'
     *
     * @param string $name The link text
     * @param string $url The link url
     * @param array $options Options. See below
     * @param array $htmlOptions Html options
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
     * - type: Asynchronously or not (Default: true)
     * - form: Serialize this form (and submit data) or not (Défault: false)
     * - submit: If defined, serialize this object (by Id) and submit data
     * - with: Addionnels parameters in request
     * - before: Called before request is initiated
     * - after: Called immediately after request was initiated and before 'loading'
     * - condition: Perform remote request conditionally by this expression. Use this to describe browser-side conditions
     *   when request should not be initiated
     * - confirm: Adds confirmation dialog
     * - cancel: Code executed if callback defined in "confirm" option returns false
     * - cache: Request Cache (Default: false)
     * - crsf: [Not yet implemented]
     */
    public function jQueryLinkToRemote($name, $url, $options = array(), $htmlOptions = array())
    {
        $htmlOptions['href'] = isset($htmlOptions['href']) ? $htmlOptions['href'] : '#';
        $htmlOptions['onclick'] = $this->jQueryRemoteFunction($url, $options) . '; return false;';

        return $this->util->tag('a', $htmlOptions, $name);
    }

    /**
     * Returns the javascript needed for a remote function.
     *
     * @param string $url Request Url
     * @param array $options Options. See JqueryHelper::jQueryLinkToRemote
     * @see JqueryHelper:jQueryLinkToRemote
     * @return string
     */
    public function jQueryRemoteFunction($url, $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            array(
                'update' => null,
                'position' => 'replace',
                'loading' => null, //Callback
                'complete' => null, //Callback
                'success' => null, //Callback
                'script' => false,
                'dataType' => null,
                'method' => 'POST',
                'type' => true,
                'form' => false,
                'submit' => null,
                'with' => null,
                'cache' => false,
                'before' => null,
                'after' => null,
                'condition' => null,
                'confirm' => null,
                'cancel' => null,
            )
        );
        $resolver->setAllowedTypes('update', array('array', 'string', 'null'));
        $resolver->setAllowedValues('position', array('before', 'after', 'top', 'bottom', 'replace'));
        $resolver->setAllowedTypes('script', array('bool'));
        $resolver->setAllowedValues('method', array('POST', 'GET', 'PUT', 'DELETE'));
        $resolver->setAllowedTypes('type', array('bool'));
        $resolver->setAllowedTypes('form', array('bool'));
        $resolver->setAllowedTypes('cache', array('bool'));
        $options = $resolver->resolve($options);

        // Defining elements to update
        if ($options['update'] && is_array($options['update'])) {
            // On success, update the element with returned data
            if (isset($options['update']['success'])) {
                $updateSuccess = "#" . $options['update']['success'];
            }

            // On failure, execute a client-side function
            if (isset($options['update']['failure'])) {
                $updateFailure = $options['update']['failure'];
            }
        } elseif ($options['update']) {
            $updateSuccess = "#" . $options['update'];
        }

        // Update method
        $updateMethod = 'html';
        switch ($options['position']) {
            case 'before':
                $updateMethod = 'before';
                break;
            case 'after':
                $updateMethod = 'after';
                break;
            case 'top':
                $updateMethod = 'prepend';
                break;
            case 'bottom':
                $updateMethod = 'append';
                break;
        }

        $execute = 'false';
        if ($options['script']) {
            $execute = 'true';
        }

        // Data Type
        if ($options['dataType']) {
            $dataType = $options['dataType'];
        } elseif ($execute) {
            $dataType = 'html';
        } else {
            $dataType = 'text';
        }

        // Is it a form submitting
        if ($options['form']) {
            $formData = 'jQuery(this).serialize()';
        } elseif ($options['submit']) {
            $formData = '{\'#' . $options['submit'] . '\'}.serialize()';
        } elseif ($options['with']) {
            $formData = $options['with'];
        }

        //Cache
        $cache = 'false';
        if ($options['cache']) {
            $cache = 'true';
        }

        // build the function
        $function = "jQuery.ajax({";
        $function .= 'type:\'' . $options['method'] . '\'';
        $function .= ',dataType:\'' . $dataType . '\'';
        $function .= ',cache: ' . $cache;
        if (!$options['type']) {
            $function .= ',async: false';
        }
        if (isset($formData)) {
            $function .= ',data:' . $formData;
        }
        if (isset($updateSuccess) && !$options['success']) {
            $function .= ',success:function(data, textStatus){jQuery(\'' . $updateSuccess . '\').' . $updateMethod . '(data);}';
        }
        if (isset($updateFailure)) {
            $function .= ',error:function(XMLHttpRequest, textStatus, errorThrown){' . $updateFailure . '}';
        }
        if ($options['loading']) {
            $function .= ',beforeSend:function(XMLHttpRequest){' . $options['loading'] . '}';
        }
        if ($options['complete']) {
            $function .= ',complete:function(XMLHttpRequest, textStatus){' . $options['complete'] . '}';
        }
        if ($options['success']) {
            $function .= ',success:function(data, textStatus){' . $options['success'] . '}';
        }
        $function .= ',url:\'' . $url . '\'';
        $function .= '})';

        if ($options['before']) {
            $function = $options['before'] . '; ' . $function;
        }
        if ($options['after']) {
            $function = $function . '; ' . $options['after'];
        }
        if ($options['condition']) {
            $function = 'if (' . $options['condition'] . ') { ' . $function . '; }';
        }
        if ($options['confirm']) {
            $function = "if (confirm('" . $this->util->escapeJavascript($options['confirm']) . "')) { $function; }";
            if ($options['cancel']) {
                $function = $function . ' else { ' . $options['cancel'] . ' }';
            }
        }

        return $function;
    }

    /**
     * Returns an html button to a remote action defined by 'url'
     *
     * @param string $name The button text
     * @param string $url The button url
     * @param array $options Options. See JqueryHelper::jQueryLinkToRemote
     * @param array $htmlOptions Html options
     * @see JqueryHelper::jQueryLinkToRemote
     * @return string
     */
    public function jQueryButtonToRemote($name, $url, $options = array(), $htmlOptions = array())
    {
        $htmlOptions['type'] = 'button';
        $htmlOptions['onclick'] = $this->jQueryRemoteFunction($url, $options) . '; return false;';

        return $this->util->tag('button', $htmlOptions, $name);
    }

    /**
     * Returns a form tag that will submit using XMLHttpRequest in the background instead of the regular
     * reloading POST arrangement.
     *
     * @param string $name The button text
     * @param array $options Options. See JqueryHelper::jQueryLinkToRemote
     * @param array $htmlOptions Html options
     * @see See JqueryHelper::jQueryLinkToRemote
     * @return string
     */
    public function jQueryFormToRemote($url, $options = array(), $htmlOptions = array())
    {
        $options['form'] = true;
        $htmlOptions['action'] = isset($htmlOptions['action']) ? $htmlOptions['action'] : $url;
        $htmlOptions['method'] = isset($htmlOptions['method']) ? $htmlOptions['method'] : 'post';
        $htmlOptions['onsubmit'] = $this->jQueryRemoteFunction($url, $options) . '; return false;';

        return $this->util->tag('form', $htmlOptions, null, true);
    }
}

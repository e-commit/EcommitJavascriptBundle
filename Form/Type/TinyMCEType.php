<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TinyMCEType extends AbstractType
{
    protected $script_url;

    public function __construct($script_url)
    {
        $this->script_url = $script_url;
    } 
    
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['script_url'] = $this->script_url;
        $view->vars['theme'] = $options['theme'];
        $view->vars['width'] = $options['width'];
        $view->vars['height'] = $options['height'];
        $view->vars['language'] = $options['language'];
        $view->vars['active_plugins'] = $options['active_plugins'];
        $view->vars['buttons1'] = $options['buttons1'];
        $view->vars['buttons2'] = $options['buttons2'];
        $view->vars['buttons3'] = $options['buttons3'];
        $view->vars['file_browser'] = $options['file_browser'];
        $view->vars['other'] = $options['other'];
    }
    
    
    public function getParent()
    {
        return 'form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'theme'             => 'advanced',
            'width'             => null,
            'height'            => null,
            'language'          => 'fr',
            'active_plugins'    => 'safari,style,table,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,pagebreak',
            'buttons1'          => 'newdocument,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,undo,redo,|,bullist,numlist,|,outdent,indent,|,print,fullscreen,preview,|,cleanup,code',
            'buttons2'          => 'formatselect,fontselect,fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,forecolor,backcolor,|,styleprops,|,nonbreaking,pagebreak',
            'buttons3'          => 'tablecontrols,|,hr,visualaid,|,sub,sup,|,charmap,emotions,iespell,image,media,advhr,|,link,unlink,anchor',
            'file_browser'      => false,
            'other'             => null,
            
            'compound'          => false,
        ));
        
        $resolver->setAllowedValues(array(
            'theme'     => array('advanced', 'simple'),
        ));
    }

    public function getName()
    {
        return 'ecommit_javascript_tinymce';
    }
}
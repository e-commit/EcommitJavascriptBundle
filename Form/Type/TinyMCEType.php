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
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Ecommit\JavascriptBundle\Form\DataTransformer\DateTimeToStringTransformer;
use Ecommit\JavascriptBundle\jQuery\Manager;

class TinyMCEType extends AbstractType
{
    protected $javascript_manager;
    
    protected $script_url;
    protected $jQuery_script_url;
    
    /**
     * Constructor
     * 
     * @param Manager $javascript_manager 
     */
    public function __construct(Manager $javascript_manager, $script_url, $jQuery_script_url)
    {
        $this->javascript_manager = $javascript_manager;
        $this->script_url = $script_url;
        $this->jQuery_script_url = $jQuery_script_url;
    }
    
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->setAttribute('script_url', $this->script_url);
        $builder->setAttribute('theme', $options['theme']);
        $builder->setAttribute('width', $options['width']);
        $builder->setAttribute('height', $options['height']);
        $builder->setAttribute('language', $options['language']);
        $builder->setAttribute('active_plugins', $options['active_plugins']);
        $builder->setAttribute('buttons1', $options['buttons1']);
        $builder->setAttribute('buttons2', $options['buttons2']);
        $builder->setAttribute('buttons3', $options['buttons3']);
        $builder->setAttribute('other', $options['other']);
    }

    
    public function buildView(FormView $view, FormInterface $form)
    {
        $this->javascript_manager->enablejQuery();
        $this->javascript_manager->addJs($this->jQuery_script_url);
        
        $view->set('script_url', $form->getAttribute('script_url'));
        $view->set('theme', $form->getAttribute('theme'));
        $view->set('width', $form->getAttribute('width'));
        $view->set('height', $form->getAttribute('height'));
        $view->set('language', $form->getAttribute('language'));
        $view->set('active_plugins', $form->getAttribute('active_plugins'));
        $view->set('buttons1', $form->getAttribute('buttons1'));
        $view->set('buttons2', $form->getAttribute('buttons2'));
        $view->set('buttons3', $form->getAttribute('buttons3'));
        $view->set('other', $form->getAttribute('other'));
    }
    
    
    public function getParent(array $options)
    {
        return 'field';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'theme'             => 'advanced',
            'width'             => null,
            'height'            => null,
            'language'          => 'fr',
            'active_plugins'    => 'safari,style,table,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,pagebreak',
            'buttons1'          => 'newdocument,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,undo,redo,|,bullist,numlist,|,outdent,indent,|,print,fullscreen,preview,|,cleanup,code',
            'buttons2'          => 'formatselect,fontselect,fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,forecolor,backcolor,|,styleprops,|,nonbreaking,pagebreak',
            'buttons3'          => 'tablecontrols,|,hr,visualaid,|,sub,sup,|,charmap,emotions,iespell,image,media,advhr,|,link,unlink,anchor',
            'other'             => null,
        );
    }

    public function getAllowedOptionValues(array $options)
    {
        return array(
            'theme'     => array(
                'advanced',
                'simple',
            ),
        );
    }
    
    public function getName()
    {
        return 'tiny_mce';
    }
}
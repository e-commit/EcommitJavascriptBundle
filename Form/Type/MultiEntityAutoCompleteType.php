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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormViewInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Doctrine\ORM\EntityManager;
use Ecommit\JavascriptBundle\jQuery\Manager;
use Ecommit\JavascriptBundle\Form\DataTransformer\EntityToMultiAutoCompleteTransformer;
use Ecommit\JavascriptBundle\Form\DataTransformer\KeyToMultiAutoCompleteTransformer;
use Ecommit\JavascriptBundle\Form\EventListener\FixMultiAutocomplete;

class MultiEntityAutoCompleteType extends AbstractType
{
    protected $javascript_manager;
    protected $em;
    
    /**
     * Constructor
     * 
     * @param Manager $javascript_manager
     * @param EntityManager $em
     */
    public function __construct(Manager $javascript_manager, EntityManager $em)
    {
        $this->javascript_manager = $javascript_manager;
        $this->em = $em;
    }
    
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $required_options = array('url', 'alias');
        foreach($required_options as $required_option)
        {
            if(empty($options[$required_option]))
            {
                throw new FormException(sprintf('The "%s" option is required', $required_option));
            }
        }
        
        if(!empty($options['query_builder']))
        {
            $query_builder = $options['query_builder'];
            $alias = $options['alias'];
        }
        elseif(!empty($options['class']))
        {
            $query_builder = $this->em->createQueryBuilder()
            ->from($options['class'], 'c')
            ->select('c');
            $alias = 'c.'.$options['alias'];
        }
        else
        {
            throw new FormException('"query_builder" or "class" option is required');
        }
        
        if($options['input'] == 'entity')
        {
            $builder->addViewTransformer(new EntityToMultiAutoCompleteTransformer($query_builder, $alias, $options['method'], $options['key_method'], $options['max']));
            $builder->addEventSubscriber(new MergeDoctrineCollectionListener());
        }
        else
        {
            $builder->addViewTransformer(new KeyToMultiAutoCompleteTransformer($query_builder, $alias, $options['method'], $options['key_method'], $options['max']));
        }
        
        //Remove prePopulate if client's value is incorrect
        $builder->addEventSubscriber(new FixMultiAutocomplete());
        
        $builder->setAttribute('url', $options['url']);
        $builder->setAttribute('hint_text', $options['hint_text']);
        $builder->setAttribute('no_results_text', $options['no_results_text']);
        $builder->setAttribute('searching_text', $options['searching_text']);
        $builder->setAttribute('theme', $options['theme']);
        $builder->setAttribute('min_chars', $options['min_chars']);
        $builder->setAttribute('max', $options['max']);
        $builder->setAttribute('prevent_duplicates', $options['prevent_duplicates']);
        $builder->setAttribute('query_param', $options['query_param']);
    }

    
    public function buildView(FormViewInterface $view, FormInterface $form, array $options)
    {
        $this->javascript_manager->enablejQuery();
        $this->javascript_manager->addJs('ejs/jQuery/tokeninput/js/jquery.tokeninput.min.js');
        
        $default_themes = array(null, 'facebook', 'mac');
        $theme = $form->getAttribute('theme');
        if(in_array($theme, $default_themes))
        {
            $file_name = (is_null($theme))? 'token-input.css' : 'token-input-'.$theme.'.css';
            $this->javascript_manager->addCss('ejs/jQuery/tokeninput/css/'.$file_name);
        }
        
        $view->setVar('url', $form->getAttribute('url'));
        $view->setVar('hint_text', $form->getAttribute('hint_text'));
        $view->setVar('no_results_text', $form->getAttribute('no_results_text'));
        $view->setVar('searching_text', $form->getAttribute('searching_text'));
        $view->setVar('theme', $theme);
        $view->setVar('min_chars', $form->getAttribute('min_chars'));
        $view->setVar('max', $form->getAttribute('max'));
        $view->setVar('prevent_duplicates', ($form->getAttribute('prevent_duplicates'))? 'true' : 'false');
        $view->setVar('query_param', $form->getAttribute('query_param'));
    }
    
    
    public function getParent()
    {
        return 'form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'input'             => 'entity',
            'url'               => null,
            'em'                => $this->em,
            'class'             => null,
            'query_builder'     => null,
            'alias'             => null,
            'method'            => '__toString',
            'key_method'        => 'getId',
            'hint_text'         => 'Type in a search term',
            'no_results_text'   => 'No results',
            'searching_text'    => 'Searching',
            'theme'             => null,
            'min_chars'         => 1,
            'max'               => 50,
            'prevent_duplicates'=> true,
            'query_param'       => 'term',
            
            //Field not required because the "html 5 error" is displayed
            //outside the screen (field outside the screen): Browser error is invisible
            'required'          => false,
            
            'compound'          => false,
        ));
        
        $resolver->setAllowedValues(array(
            'input'     => array('entity', 'key'),
        ));
    }
    
    public function getName()
    {
        return 'multi_entity_autocomplete';
    }
}
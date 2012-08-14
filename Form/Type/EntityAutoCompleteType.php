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

use Doctrine\ORM\EntityManager;
use Ecommit\JavascriptBundle\Form\DataTransformer\EntityToAutoCompleteTransformer;
use Ecommit\JavascriptBundle\Form\DataTransformer\KeyToAutoCompleteTransformer;
use Ecommit\JavascriptBundle\jQuery\Manager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntityAutoCompleteType extends AbstractType
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
        $builder->add('key', 'hidden');
        $builder->add('text', 'text');
        
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
            $builder->addViewTransformer(new EntityToAutoCompleteTransformer($query_builder, $alias, $options['method'], $options['key_method']));
        }
        else
        {
            $builder->addViewTransformer(new KeyToAutoCompleteTransformer($query_builder, $alias, $options['method'], $options['key_method']));
        }
    }

    
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $this->javascript_manager->enablejQueryUi();
        
        $view->vars['url'] = $options['url'];
        $view->vars['image_autocomplete'] = $options['image_autocomplete'];
        $view->vars['image_ok'] = $options['image_ok'];
        $view->vars['min_chars'] = $options['min_chars'];
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
            'image_autocomplete'=> 'ecr/images/i16/keyboard_magnify.png',
            'image_ok'          => 'ecr/images/i16/apply.png',
            'min_chars'         => 1,
            
            'error_bubbling'    => false,
        ));
        
        $resolver->setAllowedValues(array(
            'input'     => array('entity', 'key'),
        ));
    }
    
    public function getName()
    {
        return 'entity_autocomplete';
    }
}
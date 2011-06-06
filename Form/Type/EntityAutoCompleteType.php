<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) Hubert LECORCHE <hlecorche@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Exception\FormException;
use Doctrine\ORM\EntityManager;
use Ecommit\JavascriptBundle\jQuery\Manager;
use Ecommit\JavascriptBundle\Form\DataTransformer\EntityToAutoCompleteTransformer;

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
    
    
    public function buildForm(FormBuilder $builder, array $options)
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
        
        $builder->appendClientTransformer(new EntityToAutoCompleteTransformer($query_builder, $alias, $options['method'], $options['key_method']));
        
        $builder->setAttribute('url', $options['url']);
        $builder->setAttribute('image_autocomplete', $options['image_autocomplete']);
        $builder->setAttribute('image_ok', $options['image_ok']);
        $builder->setAttribute('min_chars', $options['min_chars']);
    }

    
    public function buildView(FormView $view, FormInterface $form)
    {
        $this->javascript_manager->enablejQueryUi();
        
        $view->set('url', $form->getAttribute('url'));
        $view->set('image_autocomplete', $form->getAttribute('image_autocomplete'));
        $view->set('image_ok', $form->getAttribute('image_ok'));
        $view->set('min_chars', $form->getAttribute('min_chars'));
    }
    
    
    public function getParent(array $options)
    {
        return 'form';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'url'               => null,
            'em'                => $this->em,
            'class'             => null,
            'query_builder'     => null,
            'alias'             => null,
            'method'            => '__toString',
            'key_method'        => 'getId',
            'image_autocomplete'=> 'ecr/images/i16/keyboard_magnify.png',
            'image_ok'          => 'ecr/images/i16/apply.png',
            'min_chars'			=> 1,
            
            'error_bubbling'    => false,
        );
    }

    public function getName()
    {
        return 'entity_autocomplete';
    }
}
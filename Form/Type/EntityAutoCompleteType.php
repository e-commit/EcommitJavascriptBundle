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

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Ecommit\JavascriptBundle\Form\DataTransformer\EntityToAutoCompleteTransformer;
use Ecommit\JavascriptBundle\Form\DataTransformer\KeyToAutoCompleteTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntityAutoCompleteType extends AbstractType
{
    protected $registry;
    
    /**
     * Constructor
     *
     * @param ManagerRegistry $em
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }
    
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('key', 'hidden');
        $builder->add('text', 'text');
        
        if($options['input'] == 'entity')
        {
            $builder->addViewTransformer(new EntityToAutoCompleteTransformer($options['query_builder'], $options['alias'], $options['render_method'], $options['key_method']));
        }
        else
        {
            $builder->addViewTransformer(new KeyToAutoCompleteTransformer($options['query_builder'], $options['alias'], $options['render_method'], $options['key_method']));
        }
    }

    
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
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
        $registry = $this->registry;
        $em_normalizer = function (Options $options, $em) use ($registry)
        {
            if(null !== $em)
            {
                return $registry->getManager($em);
            }
            return $registry->getManagerForClass($options['class']);
        };

        $query_builder_normalizer = function (Options $options, $query_builder)
        {
            $em = $options['em'];
            $class = $options['class'];
            if($query_builder == null)
            {
                $query_builder= $em->createQueryBuilder()
                ->from($class, 'c')
                ->select('c');
            }
            
            if($query_builder instanceof \Closure)
            {
                $query_builder = $query_builder($em->getRepository($class));
            }        
            if(!$query_builder instanceof QueryBuilder)
            {
                throw new InvalidConfigurationException('"query_builder" must be an instance of Doctrine\ORM\QueryBuilder');
            }
            return $query_builder;
        };
        
        $alias_normalizer = function (Options $options, $alias)
        {
            if($alias == null)
            {
                $em = $options['em'];
                $identifier = $em->getClassMetadata($options['class'])->getIdentifierFieldNames();
                if(count($identifier) != 1)
                {
                    throw new InvalidConfigurationException('"alias" option is required');
                }
                $identifier = $identifier[0];
                $query_builder = $options['query_builder'];
                $alias = current($query_builder->getRootAliases()).'.'.$identifier;
            }
            return $alias;
        };
        
        $resolver->setDefaults(array(
            'input'             => 'entity',
            'em'                => null,
            'query_builder'     => null,
            'render_method'     => '__toString',
            'key_method'        => 'getId',
            'alias'             => null,
            'image_autocomplete'=> 'ecr/images/i16/keyboard_magnify.png',
            'image_ok'          => 'ecr/images/i16/apply.png',
            'min_chars'         => 1,
            
            'error_bubbling'    => false,
        ));
        
        $resolver->setRequired(array(
            'class',
            'url',
        ));
        
        $resolver->setAllowedValues(array(
            'input'     => array('entity', 'key'),
        ));
        
        $resolver->setNormalizers(array(
            'em' => $em_normalizer,
            'query_builder' => $query_builder_normalizer,
            'alias' => $alias_normalizer,
        ));
    }
    
    public function getName()
    {
        return 'entity_autocomplete';
    }
}
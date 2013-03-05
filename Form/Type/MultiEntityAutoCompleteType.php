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
use Doctrine\ORM\QueryBuilder;
use Ecommit\JavascriptBundle\Form\DataTransformer\EntityToMultiAutoCompleteTransformer;
use Ecommit\JavascriptBundle\Form\DataTransformer\KeyToMultiAutoCompleteTransformer;
use Ecommit\JavascriptBundle\Form\EventListener\FixMultiAutocomplete;
use Ecommit\JavascriptBundle\jQuery\Manager;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MultiEntityAutoCompleteType extends AbstractType
{
    protected $javascript_manager;
    protected $registry;
    
    /**
     * Constructor
     * 
     * @param Manager $javascript_manager
     * @param ManagerRegistry $registry
     */
    public function __construct(Manager $javascript_manager, ManagerRegistry $registry)
    {
        $this->javascript_manager = $javascript_manager;
        $this->registry = $registry;
    }
    
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options['input'] == 'entity')
        {
            $builder->addViewTransformer(new EntityToMultiAutoCompleteTransformer($options['query_builder'], $options['alias'], $options['method'], $options['key_method'], $options['max']));
            $builder->addEventSubscriber(new MergeDoctrineCollectionListener());
        }
        else
        {
            $builder->addViewTransformer(new KeyToMultiAutoCompleteTransformer($options['query_builder'], $options['alias'], $options['method'], $options['key_method'], $options['max']));
        }
        
        //Remove prePopulate if client's value is incorrect
        $builder->addEventSubscriber(new FixMultiAutocomplete());
    }

    
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $this->javascript_manager->enablejQuery();
        $this->javascript_manager->addJs('ejs/jQuery/tokeninput/js/jquery.tokeninput.min.js');
        
        $default_themes = array(null, 'facebook', 'mac');
        $theme = $options['theme'];
        if(in_array($theme, $default_themes))
        {
            $file_name = (is_null($theme))? 'token-input.css' : 'token-input-'.$theme.'.css';
            $this->javascript_manager->addCss('ejs/jQuery/tokeninput/css/'.$file_name);
        }
        
        $view->vars['url'] = $options['url'];
        $view->vars['hint_text'] = $options['hint_text'];
        $view->vars['no_results_text'] = $options['no_results_text'];
        $view->vars['searching_text'] = $options['searching_text'];
        $view->vars['theme'] = $theme;
        $view->vars['min_chars'] = $options['min_chars'];
        $view->vars['max'] = $options['max'];
        $view->vars['prevent_duplicates'] = $options['prevent_duplicates']? 'true' : 'false';
        $view->vars['query_param'] = $options['query_param'];
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
        return 'multi_entity_autocomplete';
    }
}
<?php
/**
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Form\Type;

use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntitiesToIdsTransformer;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntitiesToJsonTransformer;
use Ecommit\JavascriptBundle\Form\EventListener\FixMultiAutocomplete;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TokenInputEntitiesAjaxType extends AbstractType
{
    use EntityNormalizerTrait;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var Router
     */
    protected $router;

    /**
     * Constructor
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry, Router $router)
    {
        $this->registry = $registry;
        $this->router = $router;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['input'] == 'key') {
            $builder->addModelTransformer(
                new ReversedTransformer(
                    new EntitiesToIdsTransformer(
                        $options['query_builder'],
                        $options['identifier'],
                        $options['max']
                    )
                )
            );
        }

        $builder->addViewTransformer(
            new EntitiesToJsonTransformer(
                $options['query_builder'],
                $options['identifier'],
                $options['property'],
                'id',
                'name',
                $options['max'],
                true,
                ',',
                true
            )
        );

        //Remove prePopulate if client's value is incorrect
        $builder->addEventSubscriber(new FixMultiAutocomplete());
    }


    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['url'] = $options['url'];
        $view->vars['hint_text'] = $options['hint_text'];
        $view->vars['no_results_text'] = $options['no_results_text'];
        $view->vars['searching_text'] = $options['searching_text'];
        $view->vars['theme'] = $options['theme'];
        $view->vars['min_chars'] = $options['min_chars'];
        $view->vars['max'] = $options['max'];
        $view->vars['prevent_duplicates'] = $options['prevent_duplicates'] ? 'true' : 'false';
        $view->vars['query_param'] = $options['query_param'];
    }


    public function getParent()
    {
        return 'form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $router = $this->router;
        $resolver->setDefaults(
            array(
                'input' => 'entity',
                'em' => null,
                'query_builder' => null,
                'identifier' => null,
                'property' => null,
                'hint_text' => 'Type in a search term',
                'no_results_text' => 'No results',
                'searching_text' => 'Searching',
                'theme' => null,
                'min_chars' => 1,
                'max' => 50,
                'prevent_duplicates' => true,
                'query_param' => 'term',
                'route_name' => null,
                'route_params' => array(),
                'url' => function (Options $options) use($router) {
                    return $this->getDefaultUrl($options, $router);
                },
                //Field not required because the "html 5 error" is displayed
                //outside the screen (field outside the screen): Browser error is invisible
                'required' => false,
                'compound' => false,
            )
        );

        $resolver->setRequired(
            array(
                'class',
            )
        );

        $resolver->setAllowedValues(
            array(
                'input' => array('entity', 'key'),
            )
        );

        $resolver->setAllowedTypes(
            array(
                'url' => array('string'),
                'route_params' => array('array'),
            )
        );

        $resolver->setNormalizers(
            array(
                'em' => $this->getEmNormalizer($this->registry),
                'query_builder' => $this->getQueryBuilderNormalizer(),
                'identifier' => $this->getIdentifierNormalizer(),
            )
        );
    }

    public function getName()
    {
        return 'ecommit_javascript_tokeninputentitiesajax';
    }
} 
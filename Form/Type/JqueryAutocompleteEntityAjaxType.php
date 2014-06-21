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

use Doctrine\Common\Persistence\ManagerRegistry;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntityToArrayTransformer;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntityToIdTransformer;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JqueryAutocompleteEntityAjaxType extends AbstractType
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
     *
     * @param ManagerRegistry $em
     */
    public function __construct(ManagerRegistry $registry, Router $router)
    {
        $this->registry = $registry;
        $this->router = $router;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('key', 'hidden');
        $builder->add('text', 'text');

        if ($options['input'] == 'key') {
            $builder->addModelTransformer(
                new ReversedTransformer(
                    new EntityToIdTransformer(
                        $options['query_builder'],
                        $options['identifier'],
                        false
                    )
                )
            );
        }

        $builder->addViewTransformer(
            new EntityToArrayTransformer(
                $options['query_builder'],
                $options['identifier'],
                $options['property']
            )
        );
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
        $router = $this->router;
        $resolver->setDefaults(
            array(
                'input' => 'entity',
                'em' => null,
                'query_builder' => null,
                'identifier' => null,
                'property' => null,
                'image_autocomplete' => 'bundles/ecommitjavascript/images/i16/keyboard_magnify.png',
                'image_ok' => 'bundles/ecommitjavascript/images/i16/apply.png',
                'min_chars' => 1,
                'route_name' => null,
                'route_params' => array(),
                'url' => function (Options $options) use($router) {
                    return $this->getDefaultUrl($options, $router);
                },
                'error_bubbling' => false,
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
        return 'ecommit_javascript_jqueryautocompleteentityajax';
    }
} 
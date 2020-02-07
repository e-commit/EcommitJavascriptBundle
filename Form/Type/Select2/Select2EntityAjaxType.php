<?php
/**
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Form\Type\Select2;

use Doctrine\Persistence\ManagerRegistry;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntitiesToIdsTransformer;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntitiesToJsonTransformer;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntityToIdTransformer;
use Ecommit\JavascriptBundle\Form\Type\EntityNormalizerTrait;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;

class Select2EntityAjaxType extends AbstractSelect2Type
{
    use EntityNormalizerTrait;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     *
     * @param ManagerRegistry $em
     */
    public function __construct(ManagerRegistry $registry, RouterInterface $router)
    {
        $this->registry = $registry;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['multiple']) {
            $this->multipleAddTransformers($builder, $options);
        } else {
            $this->noMultipleAddTransformers($builder, $options);
        }
    }

    protected function multipleAddTransformers(FormBuilderInterface $builder, array $options)
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
                $options['choice_label'],
                'key',
                'label',
                $options['max'],
                true,
                ',',
                false
            )
        );
    }

    protected function noMultipleAddTransformers(FormBuilderInterface $builder, array $options)
    {
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
            new EntityToIdTransformer(
                $options['query_builder'],
                $options['identifier'],
                true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $dataSelected = '';
        if (!$options['multiple'] && $options['input'] == 'entity' && $form->getData() && is_object($form->getData())) {
            $dataSelected = $this->extractLabel($form->getData(), $options['choice_label']);
        } elseif (!$options['multiple'] && $options['input'] == 'key' && $form->getNormData() && is_object($form->getNormData())) {
            $dataSelected = $this->extractLabel($form->getNormData(), $options['choice_label']);
        }

        $view->vars['url'] = $options['url'];
        $view->vars['min_chars'] = $options['min_chars'];
        $view->vars['multiple'] = $options['multiple'];
        $view->vars['attr'] = array(
            'data-selected-data' => $dataSelected,
        );
    }

    /**
     * @param object $object
     * @param string $property
     * @throws \Exception
     */
    protected function extractLabel($object, $property)
    {
        if ($property) {
            $accessor = PropertyAccess::createPropertyAccessor();

            return $accessor->getValue($object, $property);
        } elseif (method_exists($object, '__toString')) {
            return (string)$object;
        } else {
            throw new \Exception('"choice_label" option or "__toString" method must be defined"');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            array(
                'multiple' => false,
                'max' => 50,
                'error_bubbling' => false,
            )
        );

        $this->addCommonDefaultOptions($resolver, $this->registry, $this->router);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return HiddenType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ecommit_javascript_select2entityajax';
    }
}

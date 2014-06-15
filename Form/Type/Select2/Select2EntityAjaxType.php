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

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntityToIdTransformer;
use Ecommit\JavascriptBundle\Form\Type\EntityNormalizerTrait;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Select2EntityAjaxType extends AbstractSelect2Type
{
    use EntityNormalizerTrait;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     *
     * @param ManagerRegistry $em
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['input'] == 'key') {
            $builder->addModelTransformer(
                new ReversedTransformer(
                    new EntityToIdTransformer(
                        $options['query_builder'],
                        $options['root_alias'],
                        $options['identifier'],
                        false
                    )
                )
            );
        }

        $builder->addViewTransformer(
            new EntityToIdTransformer(
                $options['query_builder'],
                $options['root_alias'],
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
        if ($options['input'] == 'entity' && $form->getData() && is_object($form->getData())) {
            $dataSelected = $this->extractLabel($form->getData(), $options['property']);
        } elseif ($options['input'] == 'key' && $form->getNormData() && is_object($form->getNormData())) {
            $dataSelected = $this->extractLabel($form->getNormData(), $options['property']);
        }

        $view->vars['url'] = $options['url'];
        $view->vars['min_chars'] = $options['min_chars'];
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
            throw new \Exception('"property" option or "__toString" method must be defined"');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(
            array(
                'input' => 'entity',
                'em' => null,
                'query_builder' => null,
                'root_alias' => null,
                'identifier' => null,
                'property' => null,
                'min_chars' => 1,
                'error_bubbling' => false,
            )
        );

        $resolver->setRequired(
            array(
                'class',
                'url',
            )
        );

        $resolver->setAllowedValues(
            array(
                'input' => array('entity', 'key'),
            )
        );

        $resolver->setNormalizers(
            array(
                'em' => $this->getEmNormalizer($this->registry),
                'query_builder' => $this->getQueryBuilderNormalizer(),
                'root_alias' => $this->getRootAliasNormalizer(),
                'identifier' => $this->getIdentifierNormalizer(),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ecommit_javascript_select2entityajax';
    }
} 
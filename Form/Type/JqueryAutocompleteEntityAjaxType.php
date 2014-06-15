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
use Doctrine\ORM\QueryBuilder;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntityToArrayTransformer;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JqueryAutocompleteEntityAjaxType extends AbstractType
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
            new EntityToArrayTransformer(
                $options['query_builder'],
                $options['root_alias'],
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
        $registry = $this->registry;
        $emNormalizer = function (Options $options, $em) use ($registry) {
            if (null !== $em) {
                return $registry->getManager($em);
            }

            return $registry->getManagerForClass($options['class']);
        };

        $queryBuilderNormalizer = function (Options $options, $queryBuilder) {
            $em = $options['em'];
            $class = $options['class'];

            if ($queryBuilder == null) {
                $queryBuilder = $em->createQueryBuilder()
                    ->from($class, 'c')
                    ->select('c');
            }

            if ($queryBuilder instanceof \Closure) {
                $queryBuilder = $queryBuilder($em->getRepository($class));
            }

            if (!$queryBuilder instanceof QueryBuilder) {
                throw new InvalidConfigurationException(
                    '"query_builder" must be an instance of Doctrine\ORM\QueryBuilder'
                );
            }

            return $queryBuilder;
        };

        $rootAliasNormalizer = function (Options $options, $rootAlias) {
            if (null !== $rootAlias) {
                return $rootAlias;
            }

            $queryBuilder = $options['query_builder'];

            return current($queryBuilder->getRootAliases());
        };

        $identifierNormalizer = function (Options $options, $identifier) {
            if (null !== $identifier) {
                return $identifier;
            }

            $em = $options['em'];
            $identifiers = $em->getClassMetadata($options['class'])->getIdentifierFieldNames();
            if (count($identifiers) != 1) {
                throw new InvalidConfigurationException('"alias" option is required');
            }

            return $identifiers[0];
        };

        $resolver->setDefaults(
            array(
                'input' => 'entity',
                'em' => null,
                'query_builder' => null,
                'root_alias' => null,
                'identifier' => null,
                'property' => null,
                'image_autocomplete' => 'bundles/ecommitcrud/images/i16/keyboard_magnify.png',
                'image_ok' => 'bundles/ecommitcrud/images/i16/apply.png',
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
                'em' => $emNormalizer,
                'query_builder' => $queryBuilderNormalizer,
                'root_alias' => $rootAliasNormalizer,
                'identifier' => $identifierNormalizer,
            )
        );
    }

    public function getName()
    {
        return 'ecommit_javascript_jqueryautocompleteentityajax';
    }
} 
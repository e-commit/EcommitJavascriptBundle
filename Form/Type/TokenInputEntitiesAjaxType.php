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

use Doctrine\ORM\QueryBuilder;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntitiesToIdsTransformer;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntitiesToJsonTransformer;
use Ecommit\JavascriptBundle\Form\EventListener\FixMultiAutocomplete;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TokenInputEntitiesAjaxType extends AbstractType
{
    protected $registry;

    /**
     * Constructor
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['input'] == 'key') {
            $builder->addModelTransformer(
                new ReversedTransformer(
                    new EntitiesToIdsTransformer(
                        $options['query_builder'],
                        $options['root_alias'],
                        $options['identifier'],
                        $options['max']
                    )
                )
            );
        }

        $builder->addViewTransformer(
            new EntitiesToJsonTransformer(
                $options['query_builder'],
                $options['root_alias'],
                $options['identifier'],
                $options['property'],
                'id',
                'name',
                $options['max']
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
                'hint_text' => 'Type in a search term',
                'no_results_text' => 'No results',
                'searching_text' => 'Searching',
                'theme' => null,
                'min_chars' => 1,
                'max' => 50,
                'prevent_duplicates' => true,
                'query_param' => 'term',
                //Field not required because the "html 5 error" is displayed
                //outside the screen (field outside the screen): Browser error is invisible
                'required' => false,
                'compound' => false,
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
        return 'ecommit_javascript_tokeninputentitiesajax';
    }
} 
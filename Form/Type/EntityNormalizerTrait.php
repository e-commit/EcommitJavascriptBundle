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
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

trait EntityNormalizerTrait
{
    public function getEmNormalizer(ManagerRegistry $registry)
    {
        $emNormalizer = function (Options $options, $em) use ($registry) {
            if (null !== $em) {
                return $registry->getManager($em);
            }

            return $registry->getManagerForClass($options['class']);
        };

        return $emNormalizer;
    }

    public function getQueryBuilderNormalizer()
    {
        $queryBuilderNormalizer = function (Options $options, $queryBuilder) {
            $em = $options['em'];
            $class = $options['class'];

            if ($queryBuilder === null) {
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

        return $queryBuilderNormalizer;
    }

    public function getIdentifierNormalizer()
    {
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

        return $identifierNormalizer;
    }

    public function getDefaultUrl(Options $options, Router $router)
    {
        if (!empty($options['route_name'])) {
            return $router->generate($options['route_name'], $options['route_params']);
        }

        return null;
    }

    public function addCommonDefaultOptions(OptionsResolver $resolver, ManagerRegistry $registry, Router $router)
    {
        $resolver->setDefaults(
            array(
                'input' => 'entity',
                'em' => null,
                'query_builder' => null,
                'identifier' => null,
                'property' => null, // deprecated since 2.2, use "choice_label"
                'choice_label' => function (Options $options) {
                    // BC with the "property" option
                    if ($options['property']) {
                        trigger_error('The "property" option is deprecated since version 2.2. Use "choice_label" instead.', E_USER_DEPRECATED);

                        return $options['property'];
                    }

                    return null;
                },
                'min_chars' => 1,
                'route_name' => null,
                'route_params' => array(),
                'url' => function (Options $options) use($router) {
                    return $this->getDefaultUrl($options, $router);
                },
            )
        );

        $resolver->setRequired(
            array(
                'class',
            )
        );

        $resolver->setAllowedValues('input', array('entity', 'key'));

        $resolver->setAllowedTypes('url', array('string'));
        $resolver->setAllowedTypes('route_params', array('array'));

        $resolver->setNormalizer('em', $this->getEmNormalizer($registry));
        $resolver->setNormalizer('query_builder', $this->getQueryBuilderNormalizer());
        $resolver->setNormalizer('identifier', $this->getIdentifierNormalizer());
    }
}

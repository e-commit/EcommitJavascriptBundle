<?php
/**
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Form\DataTransformer\Entity;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class EntityToIdTransformer extends AbstractEntityTransformer
{
    protected $throwExceptionIfValueNotFoundInReverse;

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $identifier Identifier name
     * @param bool $throwExceptionIfValueNotFoundInReverse Throw Exception if value not found in reverse function
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        $identifier,
        $throwExceptionIfValueNotFoundInReverse = true
    ) {
        $this->init($queryBuilder, $identifier, null);
        $this->throwExceptionIfValueNotFoundInReverse = $throwExceptionIfValueNotFoundInReverse;
    }

    /**
     * Transforms entity to id
     *
     * @param Object $entity
     * @return string
     */
    public function transform($entity)
    {
        if (null === $entity || '' === $entity) {
            return null;
        }

        if (!is_object($entity)) {
            throw new UnexpectedTypeException($entity, 'object');
        }

        //Here, do not put the result in the cache because we must check the value in
        //reverseTransform (by QueryBuilder)

        return $this->accessor->getValue($entity, $this->identifier);
    }

    /**
     * Tranforms id to entity
     *
     * @param string $value
     * @return Object
     */
    public function reverseTransform($value)
    {
        if ('' === $value || null === $value) {
            return null;
        }

        if (!is_scalar($value)) {
            throw new TransformationFailedException('Value is not scalar');
        }

        try {
            $hash = $this->getCacheHash($value);
            if (array_key_exists($hash, $this->cachedResults)) {
                $entity = $this->cachedResults[$hash];
            } else {
                //Result not in cache

                $alias = current($this->queryBuilder->getRootAliases());
                $query = $this->queryBuilder->andWhere(
                    sprintf('%s.%s = :key_transformer', $alias, $this->identifier)
                )
                    ->setParameter('key_transformer', $value)
                    ->getQuery();

                $entity = $query->getSingleResult();
                $this->cachedResults[$hash] = $entity; //Saves result in cache
            }
        } catch (\Exception $e) {
            if ($this->throwExceptionIfValueNotFoundInReverse) {
                throw new TransformationFailedException(
                    sprintf('The entity with key "%s" could not be found or is not unique', $value)
                );
            } else {
                return null;
            }
        }

        return $entity;
    }
}
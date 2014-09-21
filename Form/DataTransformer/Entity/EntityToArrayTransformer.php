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

class EntityToArrayTransformer extends AbstractEntityTransformer
{
    protected $arrayIdentifierName;
    protected $arrayLabelName;

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $identifier Identifier name
     * @param string $property property that should be used for displaying the entities as text in the HTML element.
     * If left blank, the entity object will be cast into a string and so must have a __toString() method
     * @param string $arrayIdentifierName Key of the array which stores the entity identifier
     * @param string $arrayLabelName Key of the array which stores the entity label
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        $identifier,
        $property,
        $arrayIdentifierName = 'key',
        $arrayLabelName = 'text'
    ) {
        $this->init($queryBuilder, $identifier, $property);
        $this->arrayIdentifierName = $arrayIdentifierName;
        $this->arrayLabelName = $arrayLabelName;
    }

    /**
     * Transforms entity to array (identifier and label entity)
     * @param mixed $entity
     * @return mixed|void
     */
    public function transform($entity)
    {
        if (null === $entity || '' === $entity) {
            return array($this->arrayIdentifierName => '', $this->arrayLabelName => '');
        }

        if (!is_object($entity)) {
            throw new UnexpectedTypeException($entity, 'object');
        }

        //Here, do not put the result in the cache because we must check the value in
        //reverseTransform (by QueryBuilder)

        return array(
            $this->arrayIdentifierName => $this->accessor->getValue($entity, $this->identifier), //Identifier
            $this->arrayLabelName => $this->extractLabel($entity), //Label
        );
    }

    /**
     * Transforms array (identifier and label entity) to entity
     * @param mixed $value
     * @return mixed|void
     */
    public function reverseTransform($value)
    {
        $identifier = (isset($value[$this->arrayIdentifierName])) ? $value[$this->arrayIdentifierName] : null;

        if ('' === $identifier || null === $identifier) {
            return null;
        }

        if (!is_string($identifier)) {
            throw new TransformationFailedException('Value is not scalar');
        }

        try {
            $hash = $this->getCacheHash($identifier);
            if (array_key_exists($hash, $this->cachedResults)) {
                $entity = $this->cachedResults[$hash];
            } else {
                //Result not in cache

                $alias = current($this->queryBuilder->getRootAliases());
                $query = $this->queryBuilder->andWhere(sprintf('%s.%s = :key_transformer', $alias, $this->identifier))
                    ->setParameter('key_transformer', $identifier)
                    ->getQuery();

                $entity = $query->getSingleResult();
                $this->cachedResults[$hash] = $entity; //Saves result in cache
            }
        } catch (\Exception $e) {
            throw new TransformationFailedException(
                sprintf('The entity with key "%s" could not be found or is not unique', $identifier)
            );
        }

        return $entity;
    }
}

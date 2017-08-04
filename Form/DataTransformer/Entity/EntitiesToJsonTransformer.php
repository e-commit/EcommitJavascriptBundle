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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class EntitiesToJsonTransformer extends AbstractEntityTransformer
{
    protected $arrayIdentifierName;
    protected $arrayLabelName;
    protected $maxResults;
    protected $onlyKeysInReverse;
    protected $separatorIfOnlyKeysInReverse;
    protected $escapeValues;

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $identifier Identifier name
     * @param string $property property that should be used for displaying the entities as text in the HTML element.
     * If left blank, the entity object will be cast into a string and so must have a __toString() method
     * @param string $arrayIdentifierName Key of the array which stores the entity identifier
     * @param string $arrayLabelName Key of the array which stores the entity label
     * @param int $maxResults Maximum number of results allowed to be selected by the user
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        $identifier,
        $property,
        $arrayIdentifierName = 'id',
        $arrayLabelName = 'name',
        $maxResults = 99,
        $onlyKeysInReverse = false,
        $separatorIfOnlyKeysInReverse = ',',
        $escapeValues = false
    ) {
        $this->init($queryBuilder, $identifier, $property);
        $this->arrayIdentifierName = $arrayIdentifierName;
        $this->arrayLabelName = $arrayLabelName;
        $this->maxResults = $maxResults;
        $this->onlyKeysInReverse = $onlyKeysInReverse;
        $this->separatorIfOnlyKeysInReverse = $separatorIfOnlyKeysInReverse;
        $this->escapeValues = $escapeValues;
    }

    /**
     * Transforms Collection entities to json ([{id: 123, name: "Slurms MacKenzie"}, {id: 555, name: "Bob Hoskins"}])
     * @param Collection $collection
     * @return string (JSON)
     */
    public function transform($collection)
    {
        if (null === $collection) {
            return null;
        }

        if (!($collection instanceof Collection)) {
            throw new UnexpectedTypeException($collection, 'Doctrine\Common\Collection\Collection');
        }

        $results = array();
        foreach ($collection as $entity) {
            $results[] = array(
                $this->arrayIdentifierName => $this->displayValue(
                    $this->accessor->getValue($entity, $this->identifier)
                ),
                $this->arrayLabelName => $this->displayValue($this->extractLabel($entity)),
            );
        }

        //Here, do not put the result in the cache because we must check the value in
        //reverseTransform (by QueryBuilder)
        return json_encode($results);
    }

    protected function displayValue($value)
    {
        if ($this->escapeValues) {
            return \htmlentities(
                $value,
                ENT_QUOTES,
                'UTF-8'
            );
        } else {
            return $value;
        }
    }

    /**
     * Transforms JSON to Collection entities
     * @param string $value
     * @return Collection
     */
    public function reverseTransform($value)
    {
        $collection = new ArrayCollection();

        if ('' === $value || null === $value) {
            return $collection;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if ($this->onlyKeysInReverse) {
            $ids = \explode($this->separatorIfOnlyKeysInReverse, $value);
        } else {
            $ids = array();
            foreach (json_decode($value, true) as $subValue) {
                if (!empty($subValue[$this->arrayIdentifierName])) {
                    $ids[] = $subValue[$this->arrayIdentifierName];
                }
            }
        }

        $ids = \array_unique($ids);
        if (count($ids) == 0) {
            return $collection;
        }
        if (count($ids) > $this->maxResults) {
            throw new TransformationFailedException(
                sprintf('This collection should contain %s elements or less.', $this->maxResults)
            );
        }

        try {
            $hash = $this->getCacheHash($ids);
            if (array_key_exists($hash, $this->cachedResults)) {
                $collection = $this->cachedResults[$hash];
            } else {
                //Result not in cache

                $queryBuilderLoader = new ORMQueryBuilderLoader($this->queryBuilder);

                foreach ($queryBuilderLoader->getEntitiesByIds($this->identifier, $ids) as $entity) {
                    $collection->add($entity);
                }
                $this->cachedResults[$hash] = $collection; //Saves result in cache
            }
        } catch (\Exception $e) {
            throw new TransformationFailedException('Tranformation: Query Error');
        }

        return $collection;
    }
}

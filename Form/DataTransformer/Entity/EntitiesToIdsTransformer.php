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

class EntitiesToIdsTransformer extends AbstractEntityTransformer
{
    protected $maxResults;

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $identifier Identifier name
     * @param int $maxResults Maximum number of results allowed to be selected by the user
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        $identifier,
        $maxResults = 99
    ) {
        $this->init($queryBuilder, $identifier, null);
        $this->maxResults = $maxResults;
    }

    /**
     * Transforms Entities Collection to ids
     *
     * @param Collection $collection
     * @return string
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
            $results[] = $this->accessor->getValue($entity, $this->identifier);
        }

        //Here, do not put the result in the cache because we must check the value in
        //reverseTransform (by QueryBuilder)

        return $results;
    }

    /**
     * Transforms ids array to Collection entities
     * @param array $values
     * @return Collection
     */
    public function reverseTransform($values)
    {
        $collection = new ArrayCollection();

        if ('' === $values || null === $values) {
            return $collection;
        }

        if (!is_array($values)) {
            throw new UnexpectedTypeException($values, 'array');
        }

        if (count($values) == 0) {
            return $collection;
        }
        $values = \array_unique($values);

        try {
            $hash = $this->getCacheHash($values);
            if (array_key_exists($hash, $this->cachedResults)) {
                $collection = $this->cachedResults[$hash];
            } else {
                //Result not in cache

                $this->queryBuilder->setMaxResults($this->maxResults);
                $queryBuilderLoader = new ORMQueryBuilderLoader($this->queryBuilder);

                foreach ($queryBuilderLoader->getEntitiesByIds($this->identifier, $values) as $entity) {
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
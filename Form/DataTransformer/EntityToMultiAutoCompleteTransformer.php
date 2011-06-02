<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) Hubert LECORCHE <hlecorche@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class EntityToMultiAutoCompleteTransformer implements DataTransformerInterface
{
    protected $query_builder;
    protected $alias;
    protected $method;
    protected $key_method;
    protected $max;
    
    /**
     * Constructor
     * 
     * @param QueryBuilder $query_builder
     * @param string $alias     Alias to search query
     * @param string $method    Method to get displayed value
     * @param sting $key_method Method to get identifier
     * @param int $max_results  Maximum number of results allowed to be selected by the user
     */
    public function __construct(QueryBuilder $query_builder, $alias, $method, $key_method, $max)
    {
        $this->query_builder = $query_builder;
        $this->alias = $alias;
        $this->method = $method;
        $this->key_method = $key_method;
        $this->max = $max;
    }
    
    /**
     * Transforms entities to JSON ([{id: 123, name: "Slurms MacKenzie"}, {id: 555, name: "Bob Hoskins"}])
     * 
     * @param Collection|object $collection A collection of entities, a single entity or null
     * @return string (JSON) 
     */
    public function transform($collection)
    {
        if (null === $collection)
        {
            return null;
        }
        
        if (!($collection instanceof Collection))
        {
            throw new UnexpectedTypeException($collection, 'Doctrine\Common\Collection\Collection');
        }
        
        $key_method = $this->key_method;
        $method = $this->method;
        $results = array();
        foreach($collection as $entity)
        {
            $new_entity = array();
            $new_entity['id'] = \htmlentities($entity->$key_method());
            $new_entity['name'] = \htmlentities($entity->$method());
            $results[] = $new_entity;
        }
              
        return json_encode($results); 
    }
    
    /**
     * Tranforms string (id1,id2,id3) to entities
     * 
     * @param string $value
     * @return Array 
     */
    public function reverseTransform($value)
    {
        $collection = new ArrayCollection();
        
        if('' === $value || null === $value)
        {
            return $collection;
        }
        
        if(!is_string($value))
        {
            throw new UnexpectedTypeException($value, 'string');
        }
        
        $ids = \explode(',', $value);
        $ids = \array_unique($ids);
        
        if(count($ids) == 0)
        {
            return $collection;
        }
        
        try
        {
            $query = $this->query_builder->andWhere($this->query_builder->expr()->in($this->alias, $ids))
            ->setMaxResults($this->max)
            ->getQuery();
            
            foreach($query->execute() as $entity)
            {
                $collection->add($entity);
            }
        }
        catch(\Exception $e)
        {
            throw new TransformationFailedException('Tranformation: Query Error');
        }
        return $collection;
    }
}
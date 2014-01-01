<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\ORM\QueryBuilder;

class EntityToAutoCompleteTransformer implements DataTransformerInterface
{
    protected $query_builder;
    protected $alias;
    protected $method;
    protected $key_method;
    
    protected $results_cache = array();
    
    /**
     * Constructor
     * 
     * @param QueryBuilder $query_builder
     * @param string $alias     Alias to search query
     * @param string $method    Method to get displayed value
     * @param sting $key_method Method to get identifier
     */
    public function __construct(QueryBuilder $query_builder, $alias, $method, $key_method)
    {
        $this->query_builder = $query_builder;
        $this->alias = $alias;
        $this->method = $method;
        $this->key_method = $key_method;
    }
    
    /**
     * Transforms entity to array (key, text)
     * 
     * @param Object $entity
     * @return Array 
     */
    public function transform($entity)
    {
        if (null === $entity || '' === $entity)
        {
            return array('key' => '', 'text' => '');
        }
        
        if (!is_object($entity))
        {
            throw new UnexpectedTypeException($entity, 'object');
        }
        
        $key_method = $this->key_method;
        $method = $this->method;
        $key = $entity->$key_method();
        $text = $entity->$method();
        
        //Here, do not put the result in the cache because we must check the value in
        //reverseTransform (by QueryBuilder)
        
        return array('key' => $key, 'text' => $text); 
    }
    
    /**
     * Tranforms array (key, text) to entity
     * 
     * @param Array $value
     * @return Object 
     */
    public function reverseTransform($value)
    {
        $key = (isset($value['key']))? $value['key'] : null;
        
        if ('' === $key || null === $key)
        {
            return null;
        }
        
        if(!is_string($key))
        {
            throw new TransformationFailedException('Value is not scalar');
        }
        
        try
        {
            $hash = $this->getCacheHash($key);
            if(array_key_exists($hash, $this->results_cache))
            {
                $entity = $this->results_cache[$hash];
            }
            else
            {
                //Result not in cache
                
                $query = $this->query_builder->andWhere(sprintf('%s = :key_transformer', $this->alias))
                ->setParameter('key_transformer', $key)
                ->getQuery();

                $entity = $query->getSingleResult();
                $this->results_cache[$hash] = $entity; //Saves result in cache
            }  
        }
        catch(\Exception $e)
        {
            throw new TransformationFailedException(sprintf('The entity with key "%s" could not be found or is not unique', $key));
        }
        return $entity;
    }
    
    /**
     * Returns cache key for found result
     * @param array $id
     * @return string
     */
    protected function getCacheHash($id)
    {
        return md5(json_encode(array(
            spl_object_hash($this->query_builder),
            $this->alias,
            (string)$id,
        )));
    }
}
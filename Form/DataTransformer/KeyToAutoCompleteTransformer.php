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

use Doctrine\ORM\NoResultException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Exception\TransformationFailedException;

class KeyToAutoCompleteTransformer extends EntityToAutoCompleteTransformer
{
   /**
     * Transforms key to array (key, text)
     * 
     * @param scalar $key
     * @return Array 
     */
    public function transform($key)
    {
        if (null === $key || '' === $key)
        {
            return array('key' => '', 'text' => '');
        }
        
        if (!is_scalar($key))
        {
            throw new UnexpectedTypeException($key, 'scalar');
        }
        
        try
        {
            $hash = $this->getCacheHash($key);
            if(array_key_exists($hash, $this->results_cache))
            {
                //Result in cache
                //The cache is to avoid 3 SQL queries if reverseTransform is called (reverse - reverseTransform - reverse)
                $entity = $this->results_cache[$hash];
            }
            else
            {
                //Result not in cache
                
                //Not use directly $this->query_builder otherwise transform and 
                //reverse functions will use the same request 
                $query_builder = clone $this->query_builder;
                $query_builder->setParameters($this->query_builder->getParameters());

                $query = $query_builder->andWhere(sprintf('%s = :key_transformer', $this->alias))
                ->setParameter('key_transformer', $key)
                ->getQuery();

                $entity = $query->getSingleResult();
                $this->results_cache[$hash] = $entity; //Saves result in cache
            }
        }
        catch(NoResultException $e) {
            return array('key' => '', 'text' => '');
        }
        catch(\Exception $e)
        {
            throw new TransformationFailedException(sprintf('The entity with key "%s" could not be found', $key));
        }
        
        $key_method = $this->key_method;
        $method = $this->method;
        $key = $entity->$key_method();
        $text = $entity->$method();
        return array('key' => $key, 'text' => $text); 
    }
    
    /**
     * Tranforms array (key, text) to key
     * 
     * @param Array $value
     * @return String 
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
                //Result in cache
                //The cache is to avoid 3 SQL queries if reverseTransform is called (reverse - reverseTransform - reverse)
                $entity = $this->results_cache[$hash];
            }
            else
            {
                //Result not in cache
                
                //Not use directly $this->query_builder otherwise transform and 
                //reverse functions will use the same request 
                $query_builder = clone $this->query_builder;
                $query_builder->setParameters($this->query_builder->getParameters());

                $query = $query_builder->andWhere(sprintf('%s = :key_transformer', $this->alias))
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
        $key_method = $this->key_method;
        return $entity->$key_method();
    }
}
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

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Exception\TransformationFailedException;

class KeyToMultiAutoCompleteTransformer extends EntityToMultiAutoCompleteTransformer
{
    /**
     * Transforms keys array to JSON ([{id: 123, name: "Slurms MacKenzie"}, {id: 555, name: "Bob Hoskins"}])
     * 
     * @param Array  Keys array
     * @return string (JSON) 
     */
    public function transform($keys)
    {
        if (empty($keys))
        {
            return null;
        }
        
        if (!is_array($keys))
        {
            throw new UnexpectedTypeException($keys, 'array');
        }
		
		$key_method = $this->key_method;
        $method = $this->method;
        $results = array();
		try
        {
            //Not use directly $this->query_builder otherwise transform and 
			//reverse functions will use the same request 
			$query_builder = clone $this->query_builder;
			$query_builder->setParameters($this->query_builder->getParameters());
			
			$query = $query_builder->andWhere($this->query_builder->expr()->in($this->alias, $keys))
            ->setMaxResults($this->max)
            ->getQuery();
            
            foreach($query->execute() as $entity)
            {
                $new_entity = array();
				$new_entity['id'] = \htmlentities($entity->$key_method(), ENT_COMPAT, 'UTF-8');
				$new_entity['name'] = \htmlentities($entity->$method(), ENT_COMPAT, 'UTF-8');
				$results[] = $new_entity;
            }
        }
        catch(\Exception $e)
        {
            throw new TransformationFailedException('Tranformation: Query Error');
        }
		return json_encode($results); 
    }
    
    /**
     * Tranforms string (id1,id2,id3) to keys array
     * 
     * @param string $value
     * @return Array 
     */
    public function reverseTransform($value)
    {
        $collection = array();
        
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
        
		$key_method = $this->key_method;
        try
        {
            //Not use directly $this->query_builder otherwise transform and 
			//reverse functions will use the same request 
			$query_builder = clone $this->query_builder;
			$query_builder->setParameters($this->query_builder->getParameters());
			
			$query = $query_builder->andWhere($this->query_builder->expr()->in($this->alias, $ids))
            ->setMaxResults($this->max)
            ->getQuery();
            
            foreach($query->execute() as $entity)
            {
                $collection[] = $entity->$key_method();
            }
        }
        catch(\Exception $e)
        {
            throw new TransformationFailedException('Tranformation: Query Error');
        }
        return $collection;
    }
}
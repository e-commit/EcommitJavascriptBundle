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
use Ecommit\UtilBundle\Util\Util;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class AbstractEntityTransformer implements DataTransformerInterface
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    protected $identifier;
    protected $property;

    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    protected $cachedResults = array();

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $identifier Identifier name
     * @param string $property property that should be used for displaying the entities as text in the HTML element.
     * If left blank, the entity object will be cast into a string and so must have a __toString() method
     */
    protected function init(QueryBuilder $queryBuilder, $identifier, $property)
    {
        $this->queryBuilder = $queryBuilder;
        $this->identifier = $identifier;
        $this->property = $property;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Returns cache key for found result
     * @param string $id
     * @return string
     */
    protected function getCacheHash($id)
    {
        if (is_array($id)) {
            $id = Util::filterScalarValues($id);
            $id = array_map(
                function ($child) {
                    return (string)$child; //Converts ids from integer to string => Parameters for transform and reverse functions must be identicals
                },
                $id
            );
            sort($id);
        } else {
            $id = (string) $id;
        }

        return md5(
            json_encode(
                array(
                    spl_object_hash($this->queryBuilder),
                    $this->identifier,
                    $id,
                )
            )
        );
    }

    /**
     * Extract property that should be used for displaying the entities as text in the HTML element
     * @param object $object
     * @throws \Exception
     */
    protected function extractLabel($object)
    {
        if ($this->property) {
            if ($this->property instanceof \Closure) {
                return $this->property->__invoke($object);
            } else {
                return $this->accessor->getValue($object, $this->property);
            }
        } elseif (method_exists($object, '__toString')) {
            return (string)$object;
        } else {
            throw new \Exception('"choice_label" option or "__toString" method must be defined"');
        }
    }
}

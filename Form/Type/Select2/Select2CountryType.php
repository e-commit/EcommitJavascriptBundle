<?php
/**
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Form\Type\Select2;

use Symfony\Component\Form\Extension\Core\Type\CountryType;

class Select2CountryType extends AbstractSelect2Type
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CountryType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ecommit_javascript_select2country';
    }
}

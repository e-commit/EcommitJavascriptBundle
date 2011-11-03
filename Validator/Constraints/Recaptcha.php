<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Recaptcha extends Constraint
{

	public $message = 'Captcha invalide';

	/**
	 * {@inheritdoc}
	 */
	public function getTargets()
	{
		return Constraint::PROPERTY_CONSTRAINT;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validatedBy()
	{
		return 'ecommit_javascript.validator.constraints.recaptcha';
	}
}

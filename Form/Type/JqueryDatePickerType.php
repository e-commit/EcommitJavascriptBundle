<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Form\Type;

use Ecommit\JavascriptBundle\Form\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToArrayTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToTimestampTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JqueryDatePickerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $format_php = $options['format'];

        $builder->addViewTransformer(
            new DateTimeToStringTransformer($options['data_timezone'], $options['user_timezone'], $format_php)
        );

        if ($options['input'] === 'string') {
            $builder->addModelTransformer(
                new ReversedTransformer(
                    new DateTimeToStringTransformer($options['data_timezone'], $options['data_timezone'], 'Y-m-d')
                )
            );
        } else if ($options['input'] === 'timestamp') {
            $builder->addModelTransformer(
                new ReversedTransformer(
                    new DateTimeToTimestampTransformer($options['data_timezone'], $options['data_timezone'])
                )
            );
        } else if ($options['input'] === 'array') {
            $builder->addModelTransformer(
                new ReversedTransformer(
                    new DateTimeToArrayTransformer(
                        $options['data_timezone'],
                        $options['data_timezone'],
                        array('year', 'month', 'day')
                    )
                )
            );
        } else {
            if ($options['input'] !== 'datetime') {
                throw new InvalidConfigurationException(
                    'The "input" option must be "datetime", "string", "timestamp" or "array".'
                );
            }
        }
    }


    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $phpDates = array('d', 'g', 'I', 'm', 'n', 'F', 'Y');
        $jqueryDates = array('dd', 'd', 'DD', 'mm', 'm', 'MM', 'yy');

        $phpFormat = $options['format'];
        $jqueryFormat = str_replace($phpDates, $jqueryDates, $phpFormat);

        $view->vars['date_format'] = $jqueryFormat;
        $view->vars['change_month'] = ($options['change_month']) ? 'true' : 'false';
        $view->vars['change_year'] = ($options['change_year']) ? 'true' : 'false';
        $view->vars['first_day'] = $options['first_day'];
        $view->vars['go_to_current'] = ($options['go_to_current']) ? 'true' : 'false';
        $view->vars['number_of_months'] = $options['number_of_months'];
        $view->vars['other'] = $options['other'];
    }


    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\FormType';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'input' => 'datetime',
                'format' => 'd/m/Y',
                'data_timezone' => null,
                'user_timezone' => null,
                'change_month' => true,
                'change_year' => true,
                'first_day' => 1,
                'go_to_current' => false,
                'number_of_months' => 1,
                'other' => null,
                'compound' => false,
                // Don't modify \DateTime classes by reference, we treat
                // them like immutable value objects
                'by_reference' => false,
            )
        );

        $resolver->setAllowedValues('input', array('datetime', 'string', 'timestamp', 'array'));
    }

    public function getBlockPrefix()
    {
        return 'ecommit_javascript_jquerydatepicker';
    }
}
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
        $formatPhp = $options['format'];
        if ($options['time_format']) {
            if ($options['input'] !== 'datetime') {
                throw new InvalidConfigurationException('The time_format is compatible only with input = datetime');
            }

            $formatPhp = \sprintf('%s %s', $formatPhp, $options['time_format']);
        }

        $builder->addViewTransformer(
            new DateTimeToStringTransformer($options['data_timezone'], $options['user_timezone'], $formatPhp)
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

        $view->vars['time_format'] = null;
        $phpTimeFormat = $options['time_format'];
        if ($phpTimeFormat) {
            $phpTimes = array('a', 'A', 'g', 'G', 'h', 'H', 'i', 's', 'u', 'v');
            $jqueryTimes = array('t', 'T', 'h', 'H', 'hh', 'HH', 'mm','ss', 's', 'c', 'l');

            $view->vars['time_format'] = str_replace($phpTimes, $jqueryTimes, $phpTimeFormat);
        }
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
                'time_format' => null,
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

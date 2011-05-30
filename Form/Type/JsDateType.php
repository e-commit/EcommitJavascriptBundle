<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) Hubert LECORCHE <hlecorche@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Ecommit\JavascriptBundle\jQuery\Manager;

class JsDateType extends AbstractType
{
    protected $javascript_manager;
    
    /**
     * Constructor
     * 
     * @param Manager $javascript_manager 
     */
    public function __construct(Manager $javascript_manager)
    {
        $this->javascript_manager = $javascript_manager;
    }
    
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $array_date_php = array('d', 'g', 'I', 'm', 'n', 'F', 'Y');
        $array_date_jQuery = array('dd', 'd', 'DD', 'mm', 'm', 'MM', 'yy');
        
        $format_php = $options['format'];
        $format_jQuery = str_replace($array_date_php, $array_date_jQuery, $options['format']);
        
        $builder->appendClientTransformer(new DateTimeToStringTransformer($options['data_timezone'], $options['user_timezone'], $format_php));
        
        if ($options['input'] === 'string') {
            $builder->appendNormTransformer(new ReversedTransformer(
                new DateTimeToStringTransformer($options['data_timezone'], $options['data_timezone'], 'Y-m-d')
            ));
        } else if ($options['input'] === 'timestamp') {
            $builder->appendNormTransformer(new ReversedTransformer(
                new DateTimeToTimestampTransformer($options['data_timezone'], $options['data_timezone'])
            ));
        } else if ($options['input'] === 'array') {
            $builder->appendNormTransformer(new ReversedTransformer(
                new DateTimeToArrayTransformer($options['data_timezone'], $options['data_timezone'], array('year', 'month', 'day'))
            ));
        } else if ($options['input'] !== 'datetime') {
            throw new FormException('The "input" option must be "datetime", "string", "timestamp" or "array".');
        }
        
        $builder->setAttribute('format_jQuery', $format_jQuery);
        $builder->setAttribute('change_month', $options['change_month']);
        $builder->setAttribute('change_year', $options['change_year']);
        $builder->setAttribute('first_day', $options['first_day']);
        $builder->setAttribute('go_to_current', $options['go_to_current']);
        $builder->setAttribute('number_of_months', $options['number_of_months']);
        $builder->setAttribute('other', $options['other']);
    }

    
    public function buildView(FormView $view, FormInterface $form)
    {
        $this->javascript_manager->enablejQueryUi();
        
        $view->set('date_format', $form->getAttribute('format_jQuery'));
        $view->set('change_month', ($form->getAttribute('change_month'))? 'true' : 'false');
        $view->set('change_year', ($form->getAttribute('change_year'))? 'true' : 'false');
        $view->set('first_day', $form->getAttribute('first_day'));
        $view->set('go_to_current', ($form->getAttribute('go_to_current'))? 'true' : 'false');
        $view->set('number_of_months', $form->getAttribute('number_of_months'));
        $view->set('other', $form->getAttribute('other'));
    }
    
    
    public function getParent(array $options)
    {
        return 'field';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'input'             => 'datetime',
            'format'            => 'd/m/Y',
            'data_timezone'     => null,
            'user_timezone'     => null,
            'change_month'      => true,
            'change_year'       => true,
            'first_day'         => 1,
            'go_to_current'     => false,
            'number_of_months'  => 1,
            'other'             => null,
            // Don't modify \DateTime classes by reference, we treat
            // them like immutable value objects
            'by_reference'      => false,
        );
    }

    public function getAllowedOptionValues(array $options)
    {
        return array(
            'input'     => array(
                'datetime',
                'string',
                'timestamp',
                'array',
            ),
        );
    }
    
    public function getName()
    {
        return 'js_date';
    }
}
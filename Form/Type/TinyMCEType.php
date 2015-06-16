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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TinyMCEType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['theme'] = $options['theme'];
        $view->vars['width'] = $options['width'];
        $view->vars['height'] = $options['height'];
        $view->vars['language'] = $options['language'];
        $view->vars['plugins'] = $options['plugins'];
        $view->vars['toolbar1'] = $options['toolbar1'];
        $view->vars['toolbar2'] = $options['toolbar2'];
        $view->vars['file_browser'] = $options['file_browser'];
        $view->vars['other'] = $options['other'];
    }


    public function getParent()
    {
        return 'textarea';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'theme' => 'modern',
                'width' => null,
                'height' => null,
                'language' => 'fr_FR',
                'plugins' => 'advlist autolink lists link image charmap print preview hr anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking save table contextmenu directionality emoticons template paste textcolor colorpicker textpattern',
                'toolbar1' => 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
                'toolbar2' => 'print preview media | forecolor backcolor emoticons',
                'file_browser' => false,
                'other' => null,
            )
        );
    }

    public function getName()
    {
        return 'ecommit_javascript_tinymce';
    }
}

<?php
/**
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Overlay;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractOverlay
{
    /**
     * @var bool
     */
    protected $useBootstrap;

    public function declareHtmlModal($modalId, $options = array())
    {
        $options = $this->getDeclareHtmlModalOptions($options);

        $modalId = str_replace(' ', '', $modalId);
        $closeDiv = '';
        if ($options['close_div_class']) {
            if ($options['use_bootstrap']) {
                $closeDiv = sprintf('<button type="button" class="close %s" aria-label="Close"><span aria-hidden="true">&times;</span></button>', $options['close_div_class']);
            } else {
                $closeDiv = sprintf('<div class="%s"></div>', $options['close_div_class']);
            }
        }

        if ($options['use_bootstrap']) {
            return sprintf('<div id="%s" class="crud_modal modal-dialog"><div class="modal-content">%s<div class="contentWrap">%s</div></div></div>', $modalId, $closeDiv, $options['content']);
        } else {
            return sprintf('<div id="%s" class="crud_modal">%s<div class="contentWrap">%s</div></div>', $modalId, $closeDiv, $options['content']);
        }
    }

    public abstract function declareJavascriptModal($modalId, $options);

    public abstract function openModal($modalId);

    public abstract function closeModal($modalId);

    protected function getDeclareHtmlModalOptions($options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            array(
                'close_div_class' => 'overlay-close',
                'use_bootstrap' => $this->useBootstrap,
                'content' => '',
            )
        );
        $resolver->setAllowedTypes('use_bootstrap', 'bool');

        return $resolver->resolve($options);
    }

    protected function getDeclareJavascriptModalOptions($options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            array(
                'close_div_class' => 'overlay-close',
                'js_on_open' => null,
                'js_on_close' => null,
            )
        );

        return $resolver->resolve($options);
    }
}

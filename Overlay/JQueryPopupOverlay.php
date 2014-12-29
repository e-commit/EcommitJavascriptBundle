<?php
/**
 * This file is part of the agbaug package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Overlay;

class JQueryPopupOverlay extends AbstractOverlay
{
    public function __construct($useBootstrap)
    {
        $this->useBootstrap = $useBootstrap;
    }

    public function declareJavascriptModal($modalId, $options = array())
    {
        $modalId = str_replace(' ', '', $modalId);
        $options = $this->getDeclareJavascriptModalOptions($options);

        $jsModal = \sprintf("$('#%s').popup({setzindex: false, scrolllock: true, autoopen: false", $modalId);
        $jsModal .= empty($options['js_on_open']) ? '' : sprintf(" ,onopen: function() { %s }", $options['js_on_open']);
        $jsModal .= empty($options['js_on_close']) ? '' : sprintf(" ,onclose: function() { %s }", $options['js_on_close']);
        $jsModal .= empty($options['close_div_class']) ? '' : sprintf(" ,closeelement: '.%s'", $options['close_div_class']);
        $jsModal .= '}); ';

        return $jsModal;
    }

    public function openModal($modalId)
    {
        return \sprintf("$('#%s').parents('.popup_wrapper:first').animate({scrollTop: 0}); $('#%s').popup('show');", $modalId, $modalId);
    }

    public function closeModal($modalId)
    {
        return \sprintf("$('#%s').popup('hide');", $modalId);
    }
}

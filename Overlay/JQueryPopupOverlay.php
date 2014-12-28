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

    public function declareJavascriptModal($modalId, $jsOnOpen = null, $jsOnClose = null, $closeDivClass = 'overlay-close')
    {
        $modalId = str_replace(' ', '', $modalId);

        $jsModal = \sprintf("$('#%s').popup({setzindex: false, scrolllock: true, autoopen: false", $modalId);
        $jsModal .= empty($jsOnOpen) ? '' : " ,onopen: function() { $jsOnOpen }";
        $jsModal .= empty($jsOnClose) ? '' : " ,onclose: function() { $jsOnClose }";
        $jsModal .= empty($closeDivClass) ? '' : " ,closeelement: '.$closeDivClass'";
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

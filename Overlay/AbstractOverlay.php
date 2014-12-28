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

abstract class AbstractOverlay implements OverlayInterface
{
    /**
     * @var bool
     */
    protected $useBootstrap;

    public function declareHtmlModal($modalId, $content = null, $closeDivClass = 'overlay-close', $useBootstrap = null)
    {
        if ($useBootstrap === null) {
            //Default value
            $useBootstrap = $this->useBootstrap;
        }

        $modalId = str_replace(' ', '', $modalId);
        $closeDiv = '';
        if ($closeDivClass) {
            if ($useBootstrap) {
                $closeDiv = sprintf('<button type="button" class="close %s" aria-label="Close"><span aria-hidden="true">&times;</span></button>', $closeDivClass);
            } else {
                $closeDiv = sprintf('<div class="%s"></div>', $closeDivClass);
            }
        }

        if ($useBootstrap) {
            return sprintf('<div id="%s" class="crud_modal modal-dialog"><div class="modal-content">%s<div class="contentWrap">%s</div></div></div>', $modalId, $closeDiv, $content);
        } else {
            return sprintf('<div id="%s" class="crud_modal">%s<div class="contentWrap">%s</div></div>', $modalId, $closeDiv, $content);
        }
    }
}

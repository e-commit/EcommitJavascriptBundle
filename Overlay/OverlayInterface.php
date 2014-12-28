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

interface OverlayInterface
{
    public function declareHtmlModal($modalId, $content, $closeDivClass, $useBootstrap = null);

    public function declareJavascriptModal($modalId, $jsOnOpen, $jsOnClose, $closeDivClass);

    public function openModal($modalId);

    public function closeModal($modalId);
}

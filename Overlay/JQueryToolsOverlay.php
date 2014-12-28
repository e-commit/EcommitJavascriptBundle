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

class JQueryToolsOverlay extends AbstractOverlay
{
    /**
     * @var bool
     */
    protected $useBoostrap;

    public function __construct($useBoostrap)
    {
        $this->useBoostrap = $useBoostrap;
    }

    public function declareJavascriptModal($modalId, $jsOnOpen = null, $jsOnClose = null, $closeDivClass = 'overlay-close')
    {
        $modalId = str_replace(' ', '', $modalId);
        $apiVar = $this->getApiVariableName($modalId);

        $jsModal = \sprintf("var %s = $('#%s').overlay({oneInstance: false, api: true, fixed: false", $apiVar, $modalId);
        $jsModal .= empty($jsOnOpen) ? '' : " ,onLoad: function() { $jsOnOpen }";
        $jsModal .= empty($jsOnClose) ? '' : " ,onClose: function() { $jsOnClose }";
        $jsModal .= empty($closeDivClass) ? '' : " ,close: '.$closeDivClass'";
        $jsModal .= '}); ';

        return $jsModal;
    }

    public function openModal($modalId)
    {
        $modalId = str_replace(' ', '', $modalId);
        $apiVar = $this->getApiVariableName($modalId);

        return \sprintf('%s.load();', $apiVar);
    }

    public function closeModal($modalId)
    {
        $modalId = str_replace(' ', '', $modalId);
        $apiVar = $this->getApiVariableName($modalId);

        return \sprintf('%s.close();', $apiVar);
    }

    protected function getApiVariableName($modalId)
    {
        $modalId = str_replace(' ', '', $modalId);

        return \sprintf('api_crud_modal_%s', $modalId);
    }
}

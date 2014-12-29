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
    protected $useBootstrap;

    public function __construct($useBootstrap)
    {
        $this->useBootstrap = $useBootstrap;
    }

    public function declareJavascriptModal($modalId, $options = array())
    {
        $modalId = str_replace(' ', '', $modalId);
        $apiVar = $this->getApiVariableName($modalId);
        $options = $this->getDeclareJavascriptModalOptions($options);

        $jsModal = \sprintf("var %s = $('#%s').overlay({oneInstance: false, api: true, fixed: false", $apiVar, $modalId);
        $jsModal .= empty($options['js_on_open']) ? '' : sprintf(" ,onLoad: function() { %s }", $options['js_on_open']);
        $jsModal .= empty($options['js_on_close']) ? '' : sprintf(" ,onClose: function() { %s }", $options['js_on_close']);
        $jsModal .= empty($options['close_div_class']) ? '' : sprintf(" ,close: '.%s'", $options['close_div_class']);
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

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

class RecaptchaType extends AbstractType
{
    const RECAPTCHA_API_SERVER = 'http://www.google.com/recaptcha/api';
    const RECAPTCHA_API_SECURE_SERVER = 'https://www.google.com/recaptcha/api';
    const RECAPTCHA_API_JS_SERVER = 'http://www.google.com/recaptcha/api/js/recaptcha_ajax.js';

    protected $publicKey;
    protected $secure;
    protected $enable;
    protected $language;

    public function __construct($publicKey, $secure, $enable, $language)
    {
        $this->publicKey = $publicKey;
        $this->secure = $secure;
        $this->enable = $enable;
        $this->language = $language;
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['recaptcha_enable'] = $this->enable;
        if (!$this->enable) {
            return;
        }

        if (empty($this->publicKey)) {
            throw new \Exception('Recaptcha: Public and private keys are required');
        }

        if ($this->secure) {
            $server = self::RECAPTCHA_API_SECURE_SERVER;
        } else {
            $server = self::RECAPTCHA_API_SERVER;
        }

        $view->vars['url_challenge'] = $server . '/challenge?k=' . $this->publicKey;
        $view->vars['url_noscript'] = $server . '/noscript?k=' . $this->publicKey;
        $view->vars['public_key'] = $this->publicKey;
        $view->vars['options'] = $options['options'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'options' => array(
                    'theme' => 'clean',
                    'lang' => $this->language,
                ),
                'compound' => false,
            )
        );
    }

    public function getParent()
    {
        return 'form';
    }

    public function getName()
    {
        return 'ecommit_javascript_recaptcha';
    }
}

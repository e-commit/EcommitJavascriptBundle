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
use Symfony\Component\Form\FormViewInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RecaptchaType extends AbstractType
{
    const RECAPTCHA_API_SERVER = 'http://www.google.com/recaptcha/api';
    const RECAPTCHA_API_SECURE_SERVER = 'https://www.google.com/recaptcha/api';
    const RECAPTCHA_API_JS_SERVER = 'http://www.google.com/recaptcha/api/js/recaptcha_ajax.js';
    
    protected $public_key;
    protected $secure;
    protected $enable;
    protected $language;
    
    public function __construct($public_key, $secure, $enable, $language)
    {
        $this->public_key = $public_key;
        $this->secure = $secure;
        $this->enable = $enable;
        $this->language = $language;
    }
    
    public function getPublicKey()
    {
        return $this->public_key;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->setAttribute('options', $options['options']);
    }

    public function buildView(FormViewInterface $view, FormInterface $form, array $options)
    {
        if (!$this->enable)
        {
            return;
        }
        
        if(empty($this->public_key))
        {
            throw new \Exception('Recaptcha: Public and private keys are required');
        }
        
        if ($this->secure)
        {
            $server = self::RECAPTCHA_API_SECURE_SERVER;
        }
        else
        {
            $server = self::RECAPTCHA_API_SERVER;
        }

        $view->setVar('url_challenge', $server.'/challenge?k='.$this->public_key);
        $view->setVar('url_noscript', $server.'/noscript?k='.$this->public_key);
        $view->setVar('public_key', $this->public_key);
        $view->setVar('recaptcha_enable', $this->enable);
        $view->setVar('options', $form->getAttribute('options'));
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'options' => array(
                'theme' => 'clean',
                'lang' => $this->language,
            )
        ));
    }
    
    public function getParent()
    {
        return 'field';
    }
    
    public function getName()
    {
        return 'recaptcha';
    }
}
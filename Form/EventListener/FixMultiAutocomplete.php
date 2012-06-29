<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class FixMultiAutocomplete implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(FormEvents::BIND => 'onBind');
    }
    
    public function onBind(FormEvent $event)
    {
        $form = $event->getForm();
        if(!$form->isSynchronized())
        {
            $form->setData(null);
        }
    }
}
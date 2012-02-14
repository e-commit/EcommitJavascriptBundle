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
use Symfony\Component\Form\Event\DataEvent;

class FixMultiAutocomplete implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(FormEvents::POST_BIND => 'onPostBind');
    }
    
    public function onPostBind(DataEvent $event)
    {
        $form = $event->getForm();
        if(!$form->isSynchronized())
        {
            $form->setData(null);
        }
    }
}
<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Subscriber_ForwardSubscriber
    extends Ethna_EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            Ethna_Events::CONTROLLER_FORWARD => array(
                array('forward', Ethna_Events::DEFAULT_PRIOLITY)
            ));
    }

    /**
     * @param Ethna_Event_Forward $event
     */
    public static function forward(Ethna_Event_Forward $event)
    {
        if ($event->getForward() != null) {
            $view_class_name = $event->getController()->getViewClassName($event->getForward());
            $event->getController()->setView(new $view_class_name(
                $event->getController()->getBackend(),
                $event->getForward(),
                $event->getController()->_getForwardPath($event->getForward())
            ));
            call_user_func_array(array(
                $event->getController()->getView(),
                'preforward'
            ), $event->getParams());
            $event->getController()->getView()->forward();
        }
    }
}

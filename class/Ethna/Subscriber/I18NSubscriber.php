<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Subscriber_I18NSubscriber
    extends Ethna_EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            Ethna_Events::CONTROLLER_I18N => array(
                array('setLanguage', Ethna_Events::DEFAULT_PRIOLITY)
            ));
    }

    /**
     * ロケール変更時の処理を一括して行う
     *
     * @param Ethna_Event_SetLanguage $event
     */
    public static function setLanguage(Ethna_Event_SetLanguage $event)
    {
        /* NOTE(chobie): don't use controller setter method here otherwise it will cause dispatch floods */
        $event->getController()->getI18N()
            ->setLanguage($event->getLocale(),
                $event->getSystemEncoding(),
                $event->getClientEncoding());
    }
}

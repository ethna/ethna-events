<?php

class Ethna_Events
{
    const HIGH_PRIOLITY    = 100;
    const NORMAL_PRIOLITY  = 200;
    const DEFAULT_PRIOLITY = 300;
    const LOW_PRIOLITY     = 400;

    const DEBUG = "ethna.debug";

    const CONTROLLER_TRIGGER = "ethna.controller.trigger";

    const CONTROLLER_GATEWAY_PREFIX = "ethna.controller.gateway.prefix";
    const CONTROLLER_GATEWAY_ACTIONDIR = "ethna.controller.gateway.actiondir";

    const CONTROLLER_RESOLVE_ACTION = "ethna.controller.resolve_action";

    const CONTROLLER_I18N = "ethna.controller.i18n";

    const CONTROLLER_FORWARD = "ethna.controller.forward";

    private function __construct(){}
}
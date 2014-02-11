<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Event_Gateway
    extends Ethna_Event
{
    protected $gateway;

    protected $prefix = '';

    protected $action_dir_key;

    public function __construct($gateway)
    {
        $this->gateway = $gateway;
    }

    public function getGateway()
    {
        return $this->gateway;
    }

    public function setPrefix($prefix)
    {
        return $this->prefix = $prefix;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setActionDirKey($action_dir_key)
    {
        $this->action_dir_key = $action_dir_key;
    }

    public function getActionDirKey()
    {
        return $this->action_dir_key;
    }

}
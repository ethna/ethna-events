<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Event_Trigger
    extends Ethna_Event
{
    /** @var  Ethna_Controller $controller */
    protected $controller;

    protected $default_action_name;

    protected $fallback_action_name;

    protected $gateway;

    protected $result;

    public function __construct($controller, $default_action_name, $fallback_action_name, $gateway)
    {
        $this->controller = $controller;
        $this->default_action_name = $default_action_name;
        $this->fallback_action_name = $fallback_action_name;
        $this->gateway = $gateway;
    }

    /**
     * @return \Ethna_Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return mixed
     */
    public function getDefaultActionName()
    {
        return $this->default_action_name;
    }

    /**
     * @return mixed
     */
    public function getFallbackActionName()
    {
        return $this->fallback_action_name;
    }

    /**
     * @return mixed
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }
}
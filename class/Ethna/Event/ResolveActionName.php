<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Event_ResolveActionName
    extends Ethna_Event
{
    /** @var  Ethna_Controller $controller */
    protected $controller;

    protected $action_name;

    protected $default_action_name;

    protected $fallback_action_name;

    public function __construct($controller, $default_action_name, $fallback_action_name)
    {
        $this->controller = $controller;
        $this->default_action_name = $default_action_name;
        $this->fallback_action_name = $fallback_action_name;
    }

    /**
     * @return mixed
     */
    public function getActionName()
    {
        return $this->action_name;
    }

    /**
     * @return \Ethna_Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param mixed $action_name
     */
    public function setActionName($action_name)
    {
        $this->action_name = preg_replace('/[^a-z0-9\-_]+/i', '', $action_name);
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
}
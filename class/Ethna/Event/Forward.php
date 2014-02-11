<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Event_Forward
    extends Ethna_Event
{
    /** @var  Ethna_Controller $controller */
    protected $controller;

    protected $forward;

    protected $params = array();

    public function __construct($controller, $forward_name, $params = array())
    {
        $this->controller = $controller;
        $this->forward = $forward_name;
        $this->params = $params;
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
    public function getForward()
    {
        return $this->forward;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}
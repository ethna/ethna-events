<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Event_SetLanguage
    extends Ethna_Event
{
    /** @var  Ethna_Controller $controller */
    protected $controller;

    protected $locale;

    protected $system_encoding;

    protected $client_encoding;

    public function __construct($controller, $locale, $system_encoding, $client_encoding)
    {
        $this->controller = $controller;
        $this->locale = $locale;
        $this->system_encoding = $system_encoding;
        $this->client_encoding = $client_encoding;
    }

    /**
     * @return mixed
     */
    public function getClientEncoding()
    {
        return $this->client_encoding;
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
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return mixed
     */
    public function getSystemEncoding()
    {
        return $this->system_encoding;
    }


}
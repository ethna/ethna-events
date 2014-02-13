<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Event_ActionForm
    extends Ethna_Event
{
    /** @var  Ethna_ActionForm $controller */
    protected $actionform;


    public function __construct($actionform)
    {
        $this->actionform = $actionform;
    }

    /**
     * @return Ethna_ActionForm
     */
    public function getActionForm()
    {
        return $this->actionform;
    }
}
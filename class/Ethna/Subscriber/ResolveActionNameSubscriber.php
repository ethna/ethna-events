<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Subscriber_ResolveActionNameSubscriber
    extends Ethna_EventSubscriber
{
    protected $cache = array();

    public static function getSubscribedEvents()
    {
        return array(
            Ethna_Events::CONTROLLER_RESOLVE_ACTION => array(
                array('resolveAction', Ethna_Events::DEFAULT_PRIOLITY)
            ));
    }

    /**
     * UrlHandlerもしくはFormベースのAction名解決
     *
     * EthnaのDefault実装
     *
     * @param Ethna_Event_Forward $event
     */
    public static function resolveAction(Ethna_Event_ResolveActionName $event)
    {
        $controller = $event->getController();
        if (isset($_SERVER['REQUEST_METHOD']) == false) {
            if (PHP_SAPI == "cli" && $event->getDefaultActionName()) {
                $event->setActionName($event->getDefaultActionName());
                return;
            }

            return null;
        }

        $url_handler = $controller->getUrlHandler();
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $tmp_vars = $_GET;
        } else if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $tmp_vars = $_POST;
        }

        if (empty($_SERVER['URL_HANDLER']) == false) {
            $tmp_vars['__url_handler__'] = $_SERVER['URL_HANDLER'];
            $tmp_vars['__url_info__'] = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : null;
            $tmp_vars = $url_handler->requestToAction($tmp_vars);

            if ($_SERVER['REQUEST_METHOD'] == "GET") {
                $_GET = array_merge($_GET, $tmp_vars);
            } else if ($_SERVER['REQUEST_METHOD'] == "POST") {
                $_POST = array_merge($_POST, $tmp_vars);
            }
            $_REQUEST = array_merge($_REQUEST, $tmp_vars);
        }

        if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0) {
            $http_vars = $_POST;
        } else {
            $http_vars = $_GET;
        }

        // フォーム値からリクエストされたアクション名を取得する
        $action_name = $sub_action_name = null;
        foreach ($http_vars as $name => $value) {
            if ($value == "" || strncmp($name, 'action_', 7) != 0) {
                continue;
            }

            $tmp = substr($name, 7);

            // type="image"対応
            if (preg_match('/_x$/', $name) || preg_match('/_y$/', $name)) {
                $tmp = substr($tmp, 0, strlen($tmp)-2);
            }

            // value="dummy"となっているものは優先度を下げる
            if ($value == "dummy") {
                $sub_action_name = $tmp;
            } else {
                $action_name = $tmp;
            }
        }
        if ($action_name == null) {
            $action_name = $sub_action_name;
        }

        $controller->getLogger()->log(LOG_DEBUG, 'form_action_name[%s]', $action_name);
        $default_action_name = $event->getDefaultActionName();

        // フォームからの指定が無い場合はエントリポイントに指定されたデフォルト値を利用する
        if ($action_name == "" && count($event->getDefaultActionName()) > 0) {
            $tmp = is_array($default_action_name) ? $default_action_name[0] : $default_action_name;
            if ($tmp{strlen($tmp)-1} == '*') {
                $tmp = substr($tmp, 0, -1);
            }
            $event->getController()->getLogger()->log(LOG_DEBUG, '-> default_action_name[%s]', $tmp);
            $action_name = $tmp;
        }

        // エントリポイントに配列が指定されている場合は指定以外のアクション名は拒否する
        if (is_array($default_action_name)) {
            if ($event->getController()->_isAcceptableActionName($action_name, $default_action_name) == false) {
                // 指定以外のアクション名で合った場合は$fallback_action_name(or デフォルト)
                $tmp = $event->getFallbackActionName() != "" ? $event->getFallbackActionName() : $default_action_name[0];
                if ($tmp{strlen($tmp)-1} == '*') {
                    $tmp = substr($tmp, 0, -1);
                }
                $event->getController()->logger->log(LOG_DEBUG, '-> fallback_action_name[%s]', $tmp);
                $action_name = $tmp;
            }
        }

        $event->setActionName($action_name);
    }
}

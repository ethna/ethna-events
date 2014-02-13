<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Subscriber_TriggerSubscriber
    extends Ethna_EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            Ethna_Events::CONTROLLER_TRIGGER => array(
                array('triggerWWW', Ethna_Events::DEFAULT_PRIOLITY),
                array('triggerCLI', Ethna_Events::DEFAULT_PRIOLITY),
            ),
            Ethna_Events::ACTIONFORM_SETVARS => array(
                array('setVarsWWW', Ethna_Events::DEFAULT_PRIOLITY),
                array('setVarsCLI', Ethna_Events::DEFAULT_PRIOLITY),
            ),
            Ethna_Events::CONTROLLER_GATEWAY_PREFIX => array(
                array('getWWWGatewayPrefix', Ethna_Events::DEFAULT_PRIOLITY),
                array('getCliGatewayPrefix', Ethna_Events::DEFAULT_PRIOLITY),
            ),
            Ethna_Events::CONTROLLER_GATEWAY_ACTIONDIR => array(
                array('getWWWActionDir', Ethna_Events::DEFAULT_PRIOLITY),
                array('getCliActionDir', Ethna_Events::DEFAULT_PRIOLITY),
            )
        );
    }

    public static function setVarsWWW(Ethna_Event_ActionForm $event)
    {
        $action_form = $event->getActionForm();
        if ($action_form->getBackend()->getController()->getGateway() == GATEWAY_WWW) {
            if (isset($_SERVER['REQUEST_METHOD']) == false) {
                return;
            } else if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0) {
                $http_vars = $_POST;
            } else {
                $http_vars = $_GET;
            }

            //
            //  ethna_fid というフォーム値は、フォーム定義に関わらず受け入れる
            //  これは、submitされたフォームを識別するために使われる
            //  null の場合は、以下の場合である
            //
            //  1. フォームヘルパが使われていない
            //  2. 画面の初期表示などで、submitされなかった
            //  3. {form name=...} が未設定である
            //
            $action_form->form_vars['ethna_fid'] = (isset($http_vars['ethna_fid']) == false
                || is_null($http_vars['ethna_fid']))
                ? null
                : $http_vars['ethna_fid'];

            foreach ($action_form->getDef() as $name => $def) {
                $type = is_array($def['type']) ? $def['type'][0] : $def['type'];
                if ($type == VAR_TYPE_FILE) {
                    // ファイルの場合

                    // 値の有無の検査
                    if (is_null($action_form->getFilesInfoByFormName($_FILES, $name, 'tmp_name'))) {
                        $action_form->set($name, null);
                        continue;
                    }

                    // 配列構造の検査
                    if (is_array($def['type'])) {
                        if (is_array($action_form->getFilesInfoByFormName($_FILES, $name, 'tmp_name')) == false) {
                            $action_form->handleError($name, E_FORM_WRONGTYPE_ARRAY);
                            $action_form->set($name, null);
                            continue;
                        }
                    } else {
                        if (is_array($action_form->getFilesInfoByFormName($_FILES, $name, 'tmp_name'))) {
                            $action_form->handleError($name, E_FORM_WRONGTYPE_SCALAR);
                            $action_form->set($name, null);
                            continue;
                        }
                    }

                    $files = null;
                    if (is_array($def['type'])) {
                        $files = array();
                        // ファイルデータを再構成
                        foreach (array_keys($action_form->getFilesInfoByFormName($_FILES, $name, 'name')) as $key) {
                            $files[$key] = array();
                            $files[$key]['name'] = $action_form->getFilesInfoByFormName($_FILES, $name."[".$key."]", 'name');
                            $files[$key]['type'] = $action_form->getFilesInfoByFormName($_FILES, $name."[".$key."]", 'type');
                            $files[$key]['size'] = $action_form->getFilesInfoByFormName($_FILES, $name."[".$key."]", 'size');
                            $files[$key]['tmp_name'] = $action_form->getFilesInfoByFormName($_FILES, $name."[".$key."]", 'tmp_name');
                            if ($action_form->getFilesInfoByFormName($_FILES, $name."[".$key."]", 'error') == null) {
                                // PHP 4.2.0 以前
                                $files[$key]['error'] = 0;
                            } else {
                                $files[$key]['error'] = $action_form->getFilesInfoByFormName($_FILES, $name."[".$key."]", 'error');
                            }
                        }
                    } else {
                        $files['name'] = $action_form->getFilesInfoByFormName($_FILES, $name, 'name');
                        $files['type'] = $action_form->getFilesInfoByFormName($_FILES, $name, 'type');
                        $files['size'] = $action_form->getFilesInfoByFormName($_FILES, $name, 'size');
                        $files['tmp_name'] = $action_form->getFilesInfoByFormName($_FILES, $name, 'tmp_name');
                        if ($action_form->getFilesInfoByFormName($_FILES, $name, 'error') == null) {
                            // PHP 4.2.0 以前
                            $files['error'] = 0;
                        } else {
                            $files['error'] = $action_form->getFilesInfoByFormName($_FILES, $name, 'error');
                        }
                    }

                    // 値のインポート
                    $action_form->set($name, $files);

                } else {
                    // ファイル以外の場合

                    $target_var = $action_form->getVarsByFormName($http_vars, $name);

                    // 値の有無の検査
                    if (isset($target_var) == false
                        || is_null($target_var)) {
                        $action_form->set($name, null);
                        if (isset($http_vars["{$name}_x"])
                            && isset($http_vars["{$name}_y"])) {
                            // 以前の仕様に合わせる
                            $action_form->set($name, $http_vars["{$name}_x"]);
                        }
                        continue;
                    }

                    // 配列構造の検査
                    if (is_array($def['type'])) {
                        if (is_array($target_var) == false) {
                            // 厳密には、この配列の各要素はスカラーであるべき
                            $action_form->handleError($name, E_FORM_WRONGTYPE_ARRAY);
                            $action_form->set($name, null);
                            continue;
                        }
                    } else {
                        if (is_array($target_var)) {
                            $action_form->handleError($name, E_FORM_WRONGTYPE_SCALAR);
                            $action_form->set($name, null);
                            continue;
                        }
                    }

                    // 値のインポート
                    $action_form->set($name, $target_var);
                }
            }
            $event->stopPropagation();
        }
    }

    public static function setVarsCLI(Ethna_Event_ActionForm $event)
    {
        $action_form = $event->getActionForm();
        if ($action_form->getBackend()->getController()->getGateway() == GATEWAY_CLI) {
            // TODO(chobie): よしなに引数解析して渡したいけど面倒臭いなー
        }
    }

    /**
     * WWW Gatewayに対応するキーを返す
     *
     * @param Ethna_Event_Gateway $event
     */
    public static function getWWWActionDir(Ethna_Event_Gateway $event)
    {
        if ($event->getGateway() == GATEWAY_WWW) {
            $event->setActionDirKey("action");
            $event->stopPropagation();
        }
    }

    /**
     * Cli Gatewayに対応するキーを返す
     *
     * @param Ethna_Event_Gateway $event
     */
    public static function getCliActionDir(Ethna_Event_Gateway $event)
    {
        if ($event->getGateway() == GATEWAY_CLI) {
            $event->setActionDirKey("action_cli");
            $event->stopPropagation();
        }
    }

    /**
     * WWW GatewayのPrefixを返す
     *
     * Defaultなんで特にPrefixはない
     *
     * @param Ethna_Event_Gateway $event
     */
    public static function getWWWGatewayPrefix(Ethna_Event_Gateway $event)
    {
        if ($event->getGateway() == GATEWAY_WWW) {
            $event->stopPropagation();
        }
    }

    /**
     * Cli GatewayのPrefixを返す
     *
     * @param Ethna_Event_Gateway $event
     */
    public static function getCliGatewayPrefix(Ethna_Event_Gateway $event)
    {
        if ($event->getGateway() == GATEWAY_CLI) {
            $event->setPrefix("Cli");
            $event->stopPropagation();
        }
    }

    /**
     * フレームワークの処理を実行する(WWW)
     *
     * @param Ethna_Event_Forward $event
     */
    public static function triggerWWW(Ethna_Event_Trigger $event)
    {
        if ($event->getGateway() == GATEWAY_WWW) {
            self::triggerImpl($event);
        }
    }

    /**
     * フレームワークの処理を実行する(Cli)
     *
     * @param Ethna_Event_Forward $event
     */
    public static function triggerCLI(Ethna_Event_Trigger $event)
    {
        if ($event->getGateway() == GATEWAY_CLI) {
            // TODO(chobie): さすがにVIEWは違うべ
            self::triggerImpl($event);
        }
    }

    /**
     * TODO(chobie): ちゃんとかく
     * @param Ethna_Event_Trigger $event
     */
    public static function triggerSOAP(Ethna_Event_Trigger $event)
    {
        if ($event->getGateway() == GATEWAY_SOAP) {
            // SOAPエントリクラス
            $gg = new Ethna_SOAP_GatewayGenerator();
            $script = $gg->generate();

            eval($script);

            // SOAPリクエスト処理
            $server = new SoapServer(null, array(
                'uri' => $event->getController()->getConfing()->get('url')
            ));
            $server->setClass($gg->getClassName());
            $server->handle();
            $event->setResult(0);
            $event->stopPropagation();
        }
    }

    public static function triggerImpl(Ethna_Event_Trigger $event)
    {
        $controller = $event->getController();

        // アクション名の取得
        $action_name = $controller->_getActionName($event->getDefaultActionName(), $event->getFallbackActionName());
        $action_obj = $controller->_getAction($action_name);

        if (Ethna::isError($error = $controller->verifyActionObject($action_obj, $action_name, $event->getFallbackActionName()))) {
            $event->setResult($error);
            $event->stopPropagation();
            return;
        }

        $controller->setActionName($controller->executePreActionFilter($action_name));

        $backend = $controller->getBackend();
        $session = $controller->getSession();
        $session->restore();

        // 言語切替
        $controller->getEventDispatcher()->dispatch("ethna.core.language",
            new Ethna_Event_SetLanguage($controller,
                $controller->getLocale(),
                $controller->getSystemEncoding(),
                $controller->getClientEncoding()
            )
        );

        // アクションフォーム初期化
        // フォーム定義、フォーム値設定
        $controller->setupActionForm($action_name);

        // バックエンド処理実行
        $forward_name = $backend->perform($action_name);
        $controller->executePostActionFilter($forward_name, $action_name);

        // コントローラで遷移先を決定する(オプション)
        $forward_name_params = $controller->_sortForward($action_name, $forward_name);

        // Viewへの引数があれば取り出す
        $preforward_params = array();
        if (is_array($forward_name_params)) {
            $forward_name = array_shift($forward_name_params);
            $preforward_params = $forward_name_params;
        } else {
            $forward_name = $forward_name_params;
        }

        // Viewの実行
        $controller->getEventDispatcher()->dispatch(Ethna_Events::CONTROLLER_FORWARD,
            new Ethna_Event_Forward(
                $controller,
                $forward_name,
                $preforward_params
            ));

        $event->setResult(0);
        $event->stopPropagation();
    }
}

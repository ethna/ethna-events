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
            $controller = $event->getController();

            // アクション名の取得
            $action_name = $controller->_getActionName($event->getDefaultActionName(), $event->getFallbackActionName());
            $action_obj = $controller->_getAction($action_name);

            try {
                $controller->verifyActionObject($action_obj, $action_name, $event->getFallbackActionName());
            } catch (Ethna_Exception $e) {
                $event->setResult($e);
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

    /**
     * フレームワークの処理を実行する(Cli)
     *
     * @param Ethna_Event_Forward $event
     */
    public static function triggerCLI(Ethna_Event_Trigger $event)
    {
        if ($event->getGateway() == GATEWAY_CLI) {
            // TODO(chobie): さすがにVIEWは違うべ
            self::triggerWWW($event);
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
}

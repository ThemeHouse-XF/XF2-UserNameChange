<?php

namespace ThemeHouse\UserNameChange\Listener;

use ThemeHouse\UserNameChange\Pub\Controller\ForceNameChange;
use ThemeHouse\UserNameChange\XF\Entity\User;
use XF\Mvc\Controller;
use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

/**
 * Class ControllerPreDispatch
 * @package ThemeHouse\UserNameChange\Listener
 */
class ControllerPreDispatch
{
    /**
     * @param Controller $controller
     * @param $action
     * @param ParameterBag $params
     * @throws \XF\Mvc\Reply\Exception
     */
    public static function controllerPreDispatch(Controller $controller, $action, ParameterBag $params)
    {
        /** @var User $visitor */
        $visitor = \XF::visitor();
        if ($controller instanceof AbstractController && !$controller->filter('_xfWithData',
                'bool') && $visitor->user_id && $visitor->th_unco_force_name_change) {
            if (!($controller instanceof ForceNameChange)) {
                throw $controller->exception($controller->rerouteController('ThemeHouse\UserNameChange:ForceNameChange',
                    'index'));
                #\XF::dump($controller);
                #\XF::dump($action);
            }
        }
    }
}

<?php

namespace ThemeHouse\UserNameChange\Pub\Controller;

use ThemeHouse\UserNameChange\XF\Entity\User;
use XF\Pub\Controller\AbstractController;

/**
 * Class ForceNameChange
 * @package ThemeHouse\UserNameChange\Pub\Controller
 */
class ForceNameChange extends AbstractController
{
    /**
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     * @throws \XF\PrintableException
     */
    public function actionIndex()
    {
        /** @var User $user */
        $user = \XF::visitor();

        if (!$user->th_unco_force_name_change) {
            return $this->noPermission();
        }

        if ($this->isPost()) {
            $name = $this->filter('username', 'str');

            if($name == $user->username) {
                return $this->error(\XF::phrase('th_unc_user_name_cannot_be_old_name'));
            }

            $user->username = $name;
            $user->save();
            return $this->redirect($this->getDynamicRedirect($this->buildLink('/')));
        } else {
            return $this->view('ThemeHouse\UserNameChange:ForceNameChange', 'th_unc_force_name_change');
        }
    }
}

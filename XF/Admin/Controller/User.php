<?php

namespace ThemeHouse\UserNameChange\XF\Admin\Controller;

/**
 * Class User
 * @package ThemeHouse\UserNameChange\XF\Admin\Controller
 */
class User extends XFCP_User
{
    /**
     * @param \XF\Entity\User $user
     * @return \XF\Mvc\FormAction
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function userSaveProcess(\XF\Entity\User $user)
    {
        $form = parent::userSaveProcess($user);

        $input = $this->filter([
            'user' => [
                'th_unco_force_name_change' => 'bool'
            ]
        ]);

        $form->basicEntitySave($user, $input['user']);

        return $form;
    }
}

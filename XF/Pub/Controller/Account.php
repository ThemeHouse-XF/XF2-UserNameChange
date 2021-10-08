<?php

namespace ThemeHouse\UserNameChange\XF\Pub\Controller;

use XF\Entity\User;

/**
 * Class Account
 * @package ThemeHouse\UserNameChange\XF\Pub\Controller
 */
class Account extends XFCP_Account
{
    /**
     * @param User $visitor
     * @return \XF\Mvc\FormAction
     */
    protected function accountDetailsSaveProcess(User $visitor)
    {
        /** @var \ThemeHouse\UserNameChange\XF\Entity\User $visitor */
        $form = parent::accountDetailsSaveProcess($visitor);

        $username = $this->filter('th_unc_user_name', 'str');
        if ($username && $username != $visitor->username) {
            if (!$visitor->canThUncChangeUserName($error)) {
                $form->logError($error);
            }

            $form->basicEntitySave($visitor, ['username' => $username]);
        }

        return $form;
    }

    /**
     * @param User $visitor
     * @return \XF\Mvc\FormAction
     */
    protected function savePrivacyProcess(User $visitor)
    {
        $form = parent::savePrivacyProcess($visitor);

        $input = $this->filter([
            'privacy' => [
                'th_unc_change_history' => 'str'
            ]
        ]);

        $form->basicEntitySave($visitor->Privacy, $input['privacy']);

        return $form;
    }
}

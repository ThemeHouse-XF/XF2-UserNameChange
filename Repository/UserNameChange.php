<?php

namespace ThemeHouse\UserNameChange\Repository;

use XF\Entity\User;
use XF\Mvc\Entity\Repository;

/**
 * Class UserNameChange
 * @package ThemeHouse\UserNameChange\Repository
 */
class UserNameChange extends Repository
{
    /**
     * @param $user
     * @param $oldUsername
     * @param $newUsername
     * @return \ThemeHouse\UserNameChange\Entity\UserNameChange
     * @throws \XF\PrintableException
     */
    public function logUserNameChange($user, $oldUsername, $newUsername)
    {
        /** @var \ThemeHouse\UserNameChange\Entity\UserNameChange $log */
        $log = $this->em->create('ThemeHouse\UserNameChange:UserNameChange');

        $log->user_id = $user->user_id;
        $log->old_username = $oldUsername;
        $log->new_username = $newUsername;
        $log->change_date = \XF::$time;
        $log->change_user_id = \XF::visitor()->user_id;

        $log->save();
        return $log;
    }

    /**
     * @param User $user
     * @param bool $applyDefaultLimit
     * @return \XF\Mvc\Entity\Finder
     */
    public function findUserNameChanges(User $user, $applyDefaultLimit = true)
    {
        $finder = $this->finder('ThemeHouse\UserNameChange:UserNameChange');

        $finder->where('user_id', '=', $user->user_id);

        if ($applyDefaultLimit) {
            $finder->limit(\XF::options()->thusernamechange_historyLength);
        }

        $finder->setDefaultOrder('change_date', 'DESC');

        return $finder;
    }
}
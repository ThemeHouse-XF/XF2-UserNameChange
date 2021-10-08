<?php

namespace ThemeHouse\UserNameChange\XF\Entity;

use ThemeHouse\UserNameChange\Repository\UserNameChange;
use XF\Mvc\Entity\Structure;

/**
 * Class User
 * @package ThemeHouse\UserNameChange\XF\Entity
 *
 * COLUMNS
 * @property integer th_unc_last_name_change
 * @property integer th_unc_change_count
 * @property boolean th_unco_force_name_change
 *
 * GETTERS
 * @property integer th_unc_cooldown
 * @property integer th_unc_next_change
 */
class User extends XFCP_User
{
    /**
     * @return bool
     */
    public function canViewThUncHistory()
    {
        $visitor = \XF::visitor();
        return $visitor->hasPermission('th_unc', 'viewUsernameHistory')
            && $this->isPrivacyCheckMet('th_unc_change_history', $visitor);
    }

    /**
     * @throws \XF\PrintableException
     */
    protected function _preSave()
    {
        if (!$this->isInsert() && $this->isChanged('username')) {
            /** @var UserNameChange $repo */
            $repo = $this->repository('ThemeHouse\UserNameChange:UserNameChange');
            $repo->logUserNameChange($this, $this->getExistingValue('username'), $this->username);
            $this->th_unc_change_count++;
            $this->th_unc_last_name_change = \XF::$time;
            $this->th_unco_force_name_change = false;
        }

        parent::_preSave();
    }

    /**
     * @var int
     */
    protected $thUncCooldown;

    /**
     * @return int
     */
    public function getThUncCooldown()
    {
        if ($this->thUncCooldown === null) {
            if (\XF::options()->thusernamechange_invertCooldownPerm) {
                $userGroups = $this->secondary_group_ids;
                $userGroups[] = $this->user_group_id;

                $cooldown = \XF::db()->fetchOne("
                SELECT
                  MIN(permission_value_int)
                FROM
                  xf_permission_entry
                WHERE
                  (user_group_id IN (" . join(',', $userGroups) . ") OR user_id = ?)
                  AND permission_group_id = 'th_unc'
                  AND permission_id = 'changeUsernameCooldown'
                  AND permission_value_int > 0
            ", [$this->user_id]) ?: 0;
            } else {
                $cooldown = $this->hasPermission('th_unc', 'changeUsernameCooldown');
            }

            $this->thUncCooldown = $cooldown;
        }


        return $this->thUncCooldown;
    }

    /**
     * @return int
     */
    public function getThUncNextChange()
    {
        if ($this->th_unc_cooldown == -1) {
            return -1;
        }

        return $this->th_unc_last_name_change + $this->th_unc_cooldown * 86400;
    }

    /**
     * @param null $error
     * @return bool
     */
    public function canThUncChangeUserName(&$error = null)
    {
        if (!$this->hasPermission('th_unc', 'changeUsername')) {
            $error = \XF::phrase('th_unc_you_are_not_allowed_to_change_your_username');
            return false;
        }

        if ($this->th_unc_cooldown >= 0 && \XF::$time <= $this->th_unc_next_change) {
            $error = \XF::phrase('th_unc_you_cannot_change_your_username_before_x', [
                'date' => $this->app()->templater()->fnDateTime($this->app()->templater(), $escape,
                    $this->th_unc_next_change)
            ]);

            return false;
        }

        $maxChangeCount = $this->hasPermission('th_unc', 'maxChanges');
        if ($maxChangeCount >= 0 && $this->th_unc_change_count >= $maxChangeCount) {
            $error = \XF::phrase('th_unc_you_have_reached_the_maximum_number_of_allowed_user_name_changes');
            return false;
        }

        return true;
    }

    /**
     * @var \XF\Mvc\Entity\AbstractCollection
     */
    protected $thUncHistory;

    /**
     * @return \XF\Mvc\Entity\ArrayCollection
     */
    public function getThUncLatestChanges()
    {
        if (!$this->thUncHistory) {
            /** @var UserNameChange $repo */
            $repo = $this->repository('ThemeHouse\UserNameChange:UserNameChange');
            $this->thUncHistory = $repo->findUserNameChanges($this)->fetch();
        }

        return $this->thUncHistory;
    }

    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns += [
            'th_unc_change_count' => ['type' => self::UINT, 'default' => 0, 'changeLog' => false],
            'th_unc_last_name_change' => ['type' => self::UINT, 'default' => \XF::$time, 'changeLog' => false],
            'th_unco_force_name_change' => ['type' => self::BOOL, 'default' => 0],
        ];

        $structure->getters += [
            'th_unc_cooldown' => true,
            'th_unc_next_change' => true,
            'th_unc_latest_changes' => true
        ];

        return $structure;
    }
}

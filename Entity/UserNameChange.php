<?php

namespace ThemeHouse\UserNameChange\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * Class UserNameChange
 * @package ThemeHouse\UserNameChange\Entity
 *
 * COLUMNS
 * @property int change_id
 * @property int user_id
 * @property int change_user_id
 * @property string old_username
 * @property string new_username
 * @property int change_date
 *
 * RELATIONS
 * @property \ThemeHouse\UserNameChange\XF\Entity\User User
 * @property \XF\Entity\User ChangeUser
 */
class UserNameChange extends Entity
{
    /**
     * @param null $error
     * @return bool
     */
    public function canDelete(&$error = null)
    {
        $visitor = \XF::visitor();
        if ($visitor->hasPermission('th_unc', 'deleteAny')) {
            return true;
        }

        if ($visitor->hasPermission('th_unc', 'deleteOwn') && $visitor->user_id == $this->user_id) {
            return true;
        }

        return false;
    }

    /**
     * @param null $error
     * @return bool
     */
    public function canView(&$error = null)
    {
        return true;
        // return $this->User->canViewThUncHistory();
    }

    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_th_unc_username_change';
        $structure->shortName = 'ThemeHouse\UserImprovements:UserNameChange';
        $structure->primaryKey = 'change_id';

        $visitor = \XF::visitor();

        $structure->columns = [
            'change_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'change_user_id' => ['type' => self::UINT, 'default' => $visitor->user_id],
            'user_id' => ['type' => self::UINT, 'default' => $visitor->user_id],
            'old_username' => ['type' => self::STR, 'maxLength' => 200],
            'new_username' => ['type' => self::STR, 'maxLength' => 200, 'nullable' => true, 'default' => $visitor->username],
            'change_date' => ['type' => self::UINT, 'default' => \XF::$time],
        ];

        $structure->relations = [
            'User' => [
                'type' => self::TO_ONE,
                'entity' => 'XF:User',
                'conditions' => 'user_id'
            ],
            'ChangeUser' => [
                'type' => self::TO_ONE,
                'entity' => 'XF:User',
                'conditions' => [['user_id', '=', '$change_user_id']]
            ]
        ];

        return $structure;
    }
}

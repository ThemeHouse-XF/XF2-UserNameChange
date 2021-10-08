<?php

namespace ThemeHouse\UserNameChange\Listener;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Manager;
use XF\Mvc\Entity\Structure;

/**
 * Class EntityStructure
 * @package ThemeHouse\UserNameChange\Listener
 */
class EntityStructure
{
    /**
     * @param Manager $em
     * @param Structure $structure
     */
    public static function xFUserPrivacy(Manager $em, Structure &$structure)
    {
        $structure->columns += [
            'th_unc_change_history' => ['type' => Entity::STR, 'default' => 'everyone',
                'allowedValues' => ['everyone', 'members', 'followed', 'none'],
                'verify' => 'verifyPrivacyChoice'
            ]
        ];
    }
}

<?php

namespace ThemeHouse\UserNameChange\Listener;

use XF\Entity\User;

/**
 * Class CriteriaUser
 * @package ThemeHouse\UserNameChange\Listener
 */
class CriteriaUser
{
    /**
     * @param $rule
     * @param array $data
     * @param User $user
     * @param $returnValue
     */
    public static function criteriaUser($rule, array $data, User $user, &$returnValue)
    {
        /** @var \ThemeHouse\UserNameChange\XF\Entity\User $user */
        switch ($rule) {
            case 'th_unc_user_name_changes':
                $returnValue = $user->th_unc_change_count >= $data['changes'];
                break;

            case 'th_unc_user_name_max_changes':
                $returnValue = $user->th_unc_change_count <= $data['changes'];
                break;

            case 'th_unc_user_name_last_change_days':
                $returnValue = (\XF::$time - $user->th_unc_last_name_change) / 86400 >= $data['days'];
                break;

            case 'th_unc_user_name_last_change_max_days':
                $returnValue = (\XF::$time - $user->th_unc_last_name_change) / 86400 <= $data['days'];
                break;

            case 'th_unc_change_user_name':
                $returnValue = $user->canThUncChangeUserName();
                break;

            case 'th_unc_not_change_user_name':
                $returnValue = !$user->canThUncChangeUserName();
                break;

            case 'th_unc_force_name_change_pending':
                $returnValue = $user->th_unco_force_name_change;
                break;

            case 'th_unc_not_force_name_change_pending':
                $returnValue = !$user->th_unco_force_name_change;
                break;

            default:
                break;
        }
    }
}

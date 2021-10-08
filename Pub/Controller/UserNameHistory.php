<?php

namespace ThemeHouse\UserNameChange\Pub\Controller;

use ThemeHouse\UserNameChange\Repository\UserNameChange;
use ThemeHouse\UserNameChange\XF\Entity\User;
use XF\ControllerPlugin\Delete;
use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

/**
 * Class UserNameHistory
 * @package ThemeHouse\UserNameChange\Pub\Controller
 */
class UserNameHistory extends AbstractController
{
    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionIndex(ParameterBag $params)
    {
        /** @var User $user */
        $user = $this->assertViewableUser($params['user_id']);

        if(!$user->canViewThUncHistory()) {
            return $this->noPermission();
        }

        $page = $this->filterPage();
        $perPage = \XF::options()->thusernamechange_perPage;

        /** @var UserNameChange $usernameRepo */
        $usernameRepo = $this->repository('ThemeHouse\UserNameChange:UserNameChange');
        $finder = $usernameRepo->findUserNameChanges($user, false)->limitByPage($page, $perPage);

        $viewParams = [
            'user' => $user,
            'changes' => $finder->fetch(),

            'page' => $page,
            'perPage' => $perPage,
            'total' => $finder->total()
        ];

        return $this->view('ThemeHouse\UserNameChange:User\UserNameHistory', 'th_unc_user_name_history', $viewParams);
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionDelete(ParameterBag $params)
    {
        $user = $this->assertViewableUser($params['user_id']);
        $record = $this->assertUserNameChangeExists($params['change_id']);

        if ($record->user_id !== $user->user_id || !$record->canView($error)) {
            return $this->notFound($error);
        }

        if (!$record->canDelete($error)) {
            return $this->noPermission($error);
        }

        /** @var Delete $delete */
        $delete = $this->plugin('XF:Delete');
        return $delete->actionDelete(
            $record,
            $this->buildLink('members/th-unc-user-name-history/delete', $record),
            $this->buildLink('members/th-unc-user-name-history', $record),
            $this->buildLink('members/th-unc-user-name-history', $record),
            \XF::phrase('th_unc_user_name_change_at_x',
                [
                    'date' => $this->app->templater()->fnDateTime($this->app->templater(), $escape,
                        $record->change_date)])
        );
    }

    /**
     * @param $id
     * @param null $with
     * @param null $phraseKey
     * @return \XF\Mvc\Entity\Entity|\ThemeHouse\UserNameChange\Entity\UserNameChange
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function assertUserNameChangeExists($id, $with = null, $phraseKey = null)
    {
        return $this->assertRecordExists('ThemeHouse\UserNameChange:UserNameChange', $id, $with, $phraseKey);
    }

    /**
     * @param int $userId
     * @param array $extraWith
     * @param bool $basicProfileOnly
     *
     * @return \XF\Entity\User
     *
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function assertViewableUser($userId, array $extraWith = [], $basicProfileOnly = false)
    {
        $extraWith[] = 'Option';
        $extraWith[] = 'Privacy';
        $extraWith[] = 'Profile';
        array_unique($extraWith);

        /** @var \XF\Entity\User $user */
        $user = $this->em()->find('XF:User', $userId, $extraWith);
        if (!$user) {
            throw $this->exception($this->notFound(\XF::phrase('requested_user_not_found')));
        }

        $canView = $basicProfileOnly ? $user->canViewBasicProfile($error) : $user->canViewFullProfile($error);
        if (!$canView) {
            throw $this->exception($this->noPermission($error));
        }

        return $user;
    }
}

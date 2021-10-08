<?php

namespace ThemeHouse\UserNameChange;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

/**
 * Class Setup
 * @package ThemeHouse\UserNameChange
 */
class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    /**
     *
     */
    public function installStep1()
    {
        $this->schemaManager()->createTable('xf_th_unc_username_change', function (Create $table) {
            $table->addColumn('change_id', 'int')->nullable()->autoIncrement();
            $table->addColumn('user_id', 'int')->setDefault(0);
            $table->addColumn('change_user_id', 'int')->setDefault(0);
            $table->addColumn('new_username', 'varchar', 200)->nullable();
            $table->addColumn('old_username', 'varchar', 200);
            $table->addColumn('change_date', 'int')->setDefault(0);
        });
    }

    /**
     *
     */
    public function installStep2()
    {
        $this->schemaManager()->alterTable('xf_user', function (Alter $table) {
            $table->addColumn('th_unc_change_count', 'int')->setDefault(0);
            $table->addColumn('th_unc_last_name_change', 'int')->setDefault(0);
            $table->addColumn('th_unco_force_name_change', 'bool')->setDefault(0);
        });
    }

    /**
     *
     */
    public function installStep3()
    {
        $this->schemaManager()->alterTable('xf_user_privacy', function (Alter $table) {
            $table->addColumn('th_unc_change_history', 'enum')->values([
                'everyone',
                'members',
                'followed',
                'none'
            ])->setDefault('everyone');
        });
    }

    /**
     * @throws \XF\Db\Exception
     */
    public function installStep4()
    {
        if ($this->schemaManager()->tableExists('xf_th_userimprovements_username_changes')) {
            \XF::db()->query('
                INSERT INTO
                  xf_th_unc_username_change
                SELECT
                  null,
                  user_id,
                  user_id,
                  IFNULL((
                    SELECT
                      old_username
                    FROM
                      xf_th_userimprovements_username_changes subq
                    WHERE
                      subq.user_id = changep.user_id
                      and subq.change_date > changep.change_date
                    HAVING
                      MIN(change_date)
                  ), user.username),
                  old_username,
                  change_date
                FROM
                  xf_th_userimprovements_username_changes changep
                LEFT JOIN
                  xf_user user USING(user_id)
            ');
        }
    }

    /**
     *
     */
    public function uninstallStep1()
    {
        $this->schemaManager()->dropTable('xf_th_unc_username_change');
    }

    /**
     *
     */
    public function uninstallStep2()
    {
        $this->schemaManager()->alterTable('xf_user', function (Alter $table) {
            $table->dropColumns(['th_unc_change_count', 'th_unc_last_name_change']);
        });
    }

    /**
     *
     */
    public function uninstallStep3()
    {
        $this->schemaManager()->alterTable('xf_user_privacy', function (Alter $table) {
            $table->dropColumns(['th_unc_change_history']);
        });
    }
}
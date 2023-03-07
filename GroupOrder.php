<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace GroupOrder;

use GroupOrder\Model\GroupOrderQuery;
use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Finder\Finder;
use Thelia\Install\Database;
use Thelia\Model\ConfigQuery;
use Thelia\Model\Message;
use Thelia\Model\MessageQuery;
use Thelia\Module\BaseModule;

class GroupOrder extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'grouporder';

    const EMAIL_MESSAGE_NAME = 'confirmation_group_order_main_customer';

    const EMAIL_CREATE_SUB_CUTOMER_NAME = 'confirmation_group_order_sub_customer_new';
    /*
     * You may now override BaseModuleInterface methods, such as:
     * install, destroy, preActivation, postActivation, preDeactivation, postDeactivation
     *
     * Have fun !
     */
    public function postActivation(ConnectionInterface $con = null): void
    {
        try {
            GroupOrderQuery::create()->findOne();
        } catch (\Exception $e) {
            $database = new Database($con);
            $database->insertSql(null, [__DIR__ . "/Config/thelia.sql"]);
        }

        if (null === MessageQuery::create()->findOneByName(self::EMAIL_MESSAGE_NAME)) {
            $message = new Message();

            $email_templates_dir = __DIR__ . DS . 'I18n' . DS . 'email-templates' . DS;

            $message
                ->setName(self::EMAIL_MESSAGE_NAME)
                ->setHtmlTemplateFileName(self::EMAIL_MESSAGE_NAME . '.html')
                ->setLocale('fr_FR')
                ->setTitle(self::EMAIL_MESSAGE_NAME)
                ->setSubject('Commande groupée '.ConfigQuery::getStoreName())
                ->save();

        }

        if (null === MessageQuery::create()->findOneByName(self::EMAIL_CREATE_SUB_CUTOMER_NAME)) {
            $message = new Message();

            $email_templates_dir = __DIR__ . DS . 'I18n' . DS . 'email-templates' . DS;

            $message
                ->setName(self::EMAIL_CREATE_SUB_CUTOMER_NAME)
                ->setHtmlTemplateFileName(self::EMAIL_CREATE_SUB_CUTOMER_NAME . '.html')
                ->setLocale('fr_FR')
                ->setTitle(self::EMAIL_CREATE_SUB_CUTOMER_NAME)
                ->setSubject('Commande groupée '.ConfigQuery::getStoreName())
                ->save();
        }
    }

    public function update($currentVersion, $newVersion, ConnectionInterface $con = null): void
    {
        $sqlToExecute = [];
        $finder = new Finder();
        $sort = function (\SplFileInfo $a, \SplFileInfo $b) {
            $a = strtolower(substr($a->getRelativePathname(), 0, -4));
            $b = strtolower(substr($b->getRelativePathname(), 0, -4));
            return version_compare($a, $b);
        };

        $files = $finder->name('*.sql')
            ->in(__DIR__ . "/Config/update/")
            ->sort($sort);

        foreach ($files as $file) {
            if (version_compare($file->getFilename(), $currentVersion, ">")) {
                $sqlToExecute[$file->getFilename()] = $file->getRealPath();
            }
        }

        $database = new Database($con);

        foreach ($sqlToExecute as $version => $sql) {
            $database->insertSql(null, [$sql]);
        }
    }
}

<?php
/**
 * This file is part of UnisenderORM.
 *
 * 2019 (c) Ramil Aliyakberov (RAMe0) <r@me0.biz>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */


use rame0\UniORM\Classes\ContactList;
use rame0\UniORM\Classes\Exceptions\ORMException;
use rame0\UniORM\ORM;

require_once 'config.php';
require_once $root_dir . 'vendor/autoload.php';

try {
    ORM::config($apikey);
    $inst = ORM::getInstance();

    $response = ContactList::get();
    var_dump(ORM::getRequestErrorLog());
    var_dump($response->getCollection());

//    $response->delete(719386, true);
//    var_dump($response->getCollection());
} catch (ORMException $ex) {
    echo $ex->getMessage() . PHP_EOL;
    var_dump(ORM::getRequestWarnLog());
    var_dump(ORM::getRequestErrorLog());
}
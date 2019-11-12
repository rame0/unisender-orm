<?php
/**
 * This file is part of UnisenderORM.
 *
 * 2019 (c) Ramil Aliyakberov (RAMe0) <r@me0.biz>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */


use rame0\UniORM\Classes\ContactsList;
use rame0\UniORM\Classes\Exceptions\ORMException;
use rame0\UniORM\ORM;

require_once 'config.php';
require_once $root_dir . 'vendor/autoload.php';

try {
    ORM::config($apikey);
    $inst = ORM::getInstance();

    // Get Initial lists
//    $response = ContactsList::get();
//    var_dump($response->getCollection());

    // Add new list
    $newList = new ContactsList('MyNewTestList');
    $newList->save();

    // Get lists with created list
    $response = ContactsList::get();
    var_dump($response->getCollection());

    // Update list
    $newList->setTitle('MyNewTestList-changed');
    $newList->save();

    // Get lists with updated list
    $response = ContactsList::get();
    var_dump($response->getCollection());

    // Delete list
    $newList->delete();

    // Check that list was deleted
    $response = ContactsList::get();
    var_dump($response->getCollection());

} catch (Throwable $ex) {
    echo $ex->getMessage() . PHP_EOL;
    var_dump(ORM::getRequestWarnLog());
    var_dump(ORM::getRequestErrorLog());
}
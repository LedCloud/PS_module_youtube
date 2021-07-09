<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to License,
* that is bundled with this package in the file LICENSE.txt.
* If you did not receive a copy of the license, please send an email
* to connie@diacalc.org so we can send you a copy immediately.
*
* Do not edit or add to this file.
* @author    Konstantin Toporov
* @copyright © 2019 Konstantin Toporov
* @license   LICENSE.txt
* @category  Front Office Features
*/

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'kt_youtubeproduct` (
    `id_product` int(10) NOT NULL,
    `reference` VARCHAR(20),
    `params` VARCHAR(255),
    PRIMARY KEY  (`id_product`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

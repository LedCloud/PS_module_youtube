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

if (!defined('_PS_VERSION_')) {
    exit;
}

class YTModel
{
    const TABLENAME = 'kt_youtubeproduct';
    
    public static function saveData($id_product, $params)
    {
        $db = Db::getInstance();
        
        if ($db->getRow('SELECT `id_product` FROM `'._DB_PREFIX_.self::TABLENAME.
                '` WHERE `id_product`='.(int)$id_product)) {
            return $db->update(
                self::TABLENAME,
                array(
                    'reference' => pSQL($params['reference']),
                    'params' => pSQL($params['params']),
                ),
                'id_product='.(int)$id_product
            );
        } else {
            return $db->insert(
                self::TABLENAME,
                array(
                        'id_product' => (int)$id_product,
                        'reference' => pSQL($params['reference']),
                        'params' => pSQL($params['params']),
                    )
            );
        }
    }
    
    public static function deleteData($id_product)
    {
        return Db::getInstance()->delete(
            self::TABLENAME,
            'id_product='.(int)$id_product
        );
    }
    
    public static function getData($id_product)
    {
        if ($row = Db::getInstance()->getRow('SELECT `reference`, `params` FROM `'._DB_PREFIX_.self::TABLENAME.
                '` WHERE `id_product`='.(int)$id_product)) {
            return array(
                'reference' => $row['reference'],
                'params' => $row['params'],
            );
        }
        
        return false;
    }
}

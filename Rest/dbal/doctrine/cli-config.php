<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Steffen
 * Date: 27-6-13
 * Time: 15:36
 * To change this template use File | Settings | File Templates.
 */

require_once 'Bootstrap.php';

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));
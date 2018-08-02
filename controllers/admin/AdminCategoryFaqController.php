<?php

class AdminCategoryFaqController extends ModuleAdminController
{
    public function __construct()
    {
        global $cookie;
        $tab = 'AdminModules';
        $token = Tools::getAdminToken($tab.(int)(Tab::getIdFromClassName($tab)).(int)($cookie->id_employee));
        Tools::redirectAdmin('index.php?controller=AdminModules&configure=ps_categoryfaq&token=' . $token);
    }
}
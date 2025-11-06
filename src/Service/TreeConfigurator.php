<?php

namespace Iamczech\EasyAdminFieldsBundle\Service;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
class TreeConfigurator
{
    public static function applyTreeLayout(Crud $crud): void
    {
        $crud
            ->overrideTemplate('crud/index', '@EasyAdminFields/crud/tree.html.twig')
            ->setSearchFields(null)
            ->setPaginatorPageSize(9999999);
    }
}

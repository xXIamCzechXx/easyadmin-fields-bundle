<?php

namespace Iamczech\EasyAdminFieldsBundle\Service;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\HttpFoundation\RequestStack;

class EmbedConfigurator
{
    public static function applyEmbedLayout(Crud $crud, RequestStack $requestStack): void
    {
        if ($requestStack->getCurrentRequest()?->query->getBoolean('embed')) {
            $crud->overrideTemplate('layout', '@EasyAdminFields/layouts/embed.html.twig');
        }
    }
}

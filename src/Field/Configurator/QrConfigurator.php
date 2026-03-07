<?php

namespace Iamczech\EasyAdminFieldsBundle\Field\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\Logo\LogoInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Iamczech\EasyAdminFieldsBundle\Field\QrField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function Symfony\Component\Translation\t;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
final class QrConfigurator implements FieldConfiguratorInterface
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator) {}

    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return QrField::class === $field->getFieldFqcn();
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        if (('' == $url = $field->getCustomOption(QrField::OPTION_URL)) && ('' == $route = $field->getCustomOption(QrField::OPTION_ROUTE))) {
            throw new \RuntimeException(sprintf('The "%s" field must must either have filled qrUrl or qrRoute via methods "setQrUrl()" and "setQrRoute()".', $field->getProperty()));
        }

        if (($context->getCrud()->getCurrentPage() == Crud::PAGE_NEW)) {
            return;
        }

        $property = $field->getCustomOption(QrField::OPTION_PROPERTY);
        $getter = sprintf('get%s', ucfirst($property));

        if ('' !== $route = $field->getCustomOption(QrField::OPTION_ROUTE)) {
            $url = $this->urlGenerator->generate($route, [$property => $entityDto->getInstance()?->$getter()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $qr = self::generate(
            new QrCode(data: $url, size: $field->getCustomOption(QrField::OPTION_SIZE)),
            $field->getCustomOption(QrField::OPTION_LOGO),
            $field->getCustomOption(QrField::OPTION_LABEL),
        );

        $field->setCustomOption(QrField::OPTION_QR, $qr);
        $field->setCustomOption(QrField::OPTION_URL, $url);

        $field->setFormTypeOptions([
            QrField::OPTION_QR => $qr,
            QrField::OPTION_URL => $url,
            QrField::OPTION_TARGET => $field->getCustomOption(QrField::OPTION_TARGET),
        ]);
    }

    public static function generate(QrCode $qrCode, ?LogoInterface $logo = null, ?LabelInterface $label = null): string
    {
        $code = new PngWriter()->write(
            $qrCode, $logo, $label
        );

        return base64_encode($code->getString());
    }
}

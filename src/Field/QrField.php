<?php

namespace Iamczech\EasyAdminFieldsBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Endroid\QrCode\Label\Font\Font;
use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\Logo\LogoInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\QrCodeInterface;
use Iamczech\EasyAdminFieldsBundle\Form\Type\QrType;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
class QrField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_QR = 'iamczech_qr';
    public const OPTION_PROPERTY = 'iamczech_qr_property';
    public const OPTION_URL = 'iamczech_qr_url';
    public const OPTION_ROUTE = 'iamczech_qr_route';
    public const OPTION_LOGO = 'iamczech_qr_logo';
    public const OPTION_LABEL = 'qiamczech_qr_label';
    public const OPTION_FONT = 'iamczech_qr_font';
    public const OPTION_SIZE = 'iamczech_qr_size';
    public const OPTION_TARGET = 'iamczech_qr_target';

    public static function new(string $propertyName, ?string $label = null): self
    {
        if (!interface_exists(QrCodeInterface::class)) {
            throw new \RuntimeException(sprintf('The bundle "%s" does not exist. Please install this bundle via: `composer require endroid/qr-code-bundle`.', QrCodeInterface::class));
        }

        return (new self())
            ->setProperty(sprintf('qr_%s', $propertyName))
            ->setQrProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath('@EasyAdminFields/field/qr.html.twig')
            ->setFormType(QrType::class)
            ->addFormTheme('@EasyAdminFields/theme/qr.html.twig')
            ->addCssFiles('@iamczech/easyadmin-fields/styles/qr.css')
            ->setFormTypeOption('mapped', false)
            ->addCssClass('h-auto')
            ->setDisabled()
            ->setDefaultColumns('col-md-6 col-xxl-5')
            ->setCustomOption(self::OPTION_QR, '')
            ->setCustomOption(self::OPTION_URL, '')
            ->setCustomOption(self::OPTION_ROUTE, '')
            ->setCustomOption(self::OPTION_LOGO, null)
            ->setCustomOption(self::OPTION_LABEL, null)
            ->setCustomOption(self::OPTION_FONT, null)
            ->setCustomOption(self::OPTION_SIZE, 120)
            ->setCustomOption(self::OPTION_TARGET, '_blank');
    }

    public function setQrProperty(?string $property): self
    {
        $this->setCustomOption(self::OPTION_PROPERTY, $property);

        return $this;
    }

    public function setQrUrl(?string $url): self
    {
        $this->setCustomOption(self::OPTION_URL, $url);

        return $this;
    }

    public function setQrRoute(?string $route): self
    {
        $this->setCustomOption(self::OPTION_ROUTE, $route);

        return $this;
    }

    public function setQrLogo(LogoInterface $logo): self
    {
        $this->setCustomOption(self::OPTION_LOGO, $logo);

        return $this;
    }

    public function setQrLabel(LabelInterface $label): self
    {
        $this->setCustomOption(self::OPTION_LABEL, $label);

        return $this;
    }

    public function setQrFont(Font $font): self
    {
        $this->setCustomOption(self::OPTION_FONT, $font);

        return $this;
    }

    public function setQrSize(?int $size): self
    {
        $this->setCustomOption(self::OPTION_SIZE, $size);

        return $this;
    }

    public function setTarget(?string $target): self
    {
        $this->setCustomOption(self::OPTION_TARGET, $target);

        return $this;
    }
}

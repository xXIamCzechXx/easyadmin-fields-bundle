<?php

namespace Iamczech\EasyAdminFieldsBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class LockedTextField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_GROUP_NAME = 'groupName';

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath('admin/field/name.html.twig')
            ->setFormType(TextType::class)
            ->setFormTypeOptions([
                'attr.data-controller' => 'iamczech--easyadmin-fields-bundle--locked',
                'attr.data-unlock-group' => 'default',
                'attr.locked' => true,
                'attr.data-confirm-text' => 'Povolit editaci titulů, jména a příjmení.#newline#Editaci titulů, jména a příjmení provádějte pouze v případě, že jde stále o tutéž osobu, tedy když opravujete překlepy, doplňujete tituly, žena se vdala a má nové příjmení. Pokud jde o jinou osobu, ale se stejným e-mailem (např. starosta@hornidolni.cz), použijte tlačíko "Stejný email, nový uživatel".',
            ])
            ->addCssClass('field-text')
            ->setDefaultColumns('col-md-6 col-xxl-5')
            ->setCustomOption(self::OPTION_GROUP_NAME, 'default');
    }

    public function setUnlockGroup(?string $group): self
    {
        $this->setFormTypeOption('attr.data-unlock-group', $group);

        return $this;
    }
}

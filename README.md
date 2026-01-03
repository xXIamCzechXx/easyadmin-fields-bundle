# 🧹 iamczech/easyadmin-fields-bundle

A **custom fields extension bundle** for [EasyAdmin](https://github.com/EasyCorp/EasyAdminBundle) — built for modern **Symfony 7.4+** and **PHP 8.4+**, fully compatible with the new **AssetMapper** pipeline.

This is my first bundle, if I have done something wrong or incorrectly, please contact me and I will be happy to fix it.
This bundle provides advanced and interactive form fields designed to improve the developer experience and user interface inside the EasyAdmin backend.

A big thanks to a bundle from TCM-dev, BurningDog, evotodi, Yonn-Trimoreau for creating this awesome bundle –> https://github.com/TCM-dev/EasyAdminFieldsBundle/tree/main
It works great with WebpackEncore and this bundle has been created inspired by this bundle.

---

## 🚀 Requirements

* **PHP** ≥ 8.4
* **Symfony** ≥ 7.4
* **EasyAdminBundle** ≥ 4.0
* Compatible with **AssetMapper** (no Webpack Encore is not supported!)
* Works with **Stimulus 3** and **Hotwired**

---

## 🧱 Installation

### 1️⃣ Install via Composer

```bash
composer require iamczech/easyadmin-fields-bundle
```

### 2️⃣ Install front-end controllers with AssetMapper

If you're using Symfony’s **AssetMapper** (recommended):

```bash
php bin/console importmap:install
php bin/console asset-map:compile
```

The bundle automatically registers its Stimulus controllers in your `controllers.json` under:

```json
"symfony": {
    "controllers": {
        "dependent": {
            "main": "dist/dependent_controller.js",
            "webpackMode": "lazy",
            "fetch": "lazy",
            "enabled": true
        },
        "locked": {
            "main": "dist/locked_controller.js",
            "webpackMode": "lazy",
            "fetch": "lazy",
            "enabled": true
        },
        "embed": {
            "main": "dist/embed_controller.js",
            "webpackMode": "lazy",
            "fetch": "lazy",
            "enabled": true
        }
    }
}
```

---

## 🧹 Included Fields

### 🔁 DependentField

A **reactive EasyAdmin field** that allows one field to depend on another —
for example, dynamically updating a `<select>` field’s options based on a previous input.
Important thing, DependentField and its Associated fields cannot be ->autocomplete() (dependencies can), it has its own EventSubscriber to prevent from fetching large amount of data.

**Usage:**

```php
use App\Field\DependentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

yield AssociationField::new('client', 'Client')
    ->autocomplete();

yield DependentField::adapt(
    AssociationField::new('user', 'User'),
    [
        'callback_url' => $this->generateUrl('users_by_client'),
        'dependencies' => ['client'],
        'fetch_on_init' => false
    ]
);
```

🧠 **Notes:**

* Works seamlessly with AssetMapper (no JS bundler required)
* Updates target field values asynchronously via Fetch API
* Handles normal `<select>` (TomSelect (autocomplete) inputs are about to be available in next versions)

---

### 🔒 LockedTextField

A **read-only text field** that is locked by default.
When the user clicks the field, a confirmation popup appears.
After confirming, all fields in the same unlock group become editable.

**Usage:**

```php
use App\Field\LockedTextField;

yield LockedTextField::new('firstName', 'First Name')
    ->setUnlockGroup('name')
    ->setContentText('Do you really want to unlock this group of fields via first name?')
    ->setConfirmText('Oh yes!')
    ->setCancelText('Oh no!');
yield LockedTextField::new('lastName', 'Last Name')
    ->setUnlockGroup('name')
    ->setContentText('Do you really want to unlock this group of fields via last name?')
    ->setConfirmText('I agree!')
    ->setCancelText('No, thanks!');
```

The controller automatically handles:

* Displaying confirmation (via `window.confirm`)
* Unlocking all inputs with the same `data-unlock-group` value
* Removing the `locked` and `readonly` attributes dynamically

---

### 🧩 **EmbedField**
Embed nested `CrudController` directly into forms (inline CRUD editing).
It is important to set callback url for a iframe via setCallbackUrl() and generate it via adminUrlGenerator.
The default page is Crud::PAGE_INDEX action, but you can change it via the setAction() method.

**Usage:**

```php
yield EmbedField::new('entities')
    ->setCallbackUrl($this->adminUrlGenerator
        ->unsetAll() // important for embedded forms to work, so they do not inherit the filters from the parent!
        ->setController(EntityCrudController::class)
```

NOTE: To render only content of EA you need to override default layout by overriding configureCrud method in your CrudController or AbstractCrudController if you have one.

```php
public function configureCrud(Crud $crud): Crud
{
    $crud = parent::configureCrud($crud);

    EmbedConfigurator::applyEmbedLayout($crud, $this->container->get('request_stack'));

    return $crud;
}
```

### 🌳 **TreeConfigurator**
You must use `NestedSetEntity` and implement `TreeInterface` directly into your entity where you want to show tree hierarchy.
It is highly recommended to use `Gedmo\Tree` annotation with Strategy::NESTED.

**Usage:**

```php
use Gedmo\Tree\Traits\NestedSetEntity;
use Iamczech\EasyAdminFieldsBundle\Interface\TreeInterface;

#[Gedmo\Tree(type: Strategy::NESTED)]
#[ORM\Entity(repositoryClass: NestedTreeRepository::class)]
class Category implements TreeInterface
{
    use NestedSetEntity;
    
```

NOTE: Create your own TreeController and extend AbstractTreeController, then you can override reorder() method if necessary.

```php
use Iamczech\EasyAdminFieldsBundle\Controller\AbstractTreeController;

class TreeController extends AbstractTreeController
{
    // Do something or override reorder() method
}
```

Last you need to extend your crud controller and appy TreeConfigurator.

```php
public function configureCrud(Crud $crud): Crud
{
    $crud = parent::configureCrud($crud);

    TreeConfigurator::applyTreeLayout($crud);

    return $crud;
}
```

### 🔗 **LinkField**
Render a related entity as a selectable field (ChoiceField) enhanced with a dynamic action link (supported actions: edit, detail).
The field automatically generates per-entity URLs using AdminUrlGenerator and respects user permissions.

Only URLs the user is allowed to access (based on the configured CRUD action) are exposed to the frontend.

**Usage:**

```php
yield LinkField::link(AssociationField::new('person', 'Person'), [
        LinkField::URL => $this->adminUrlGenerator->setController(SomeCrudController::class)->setAction(Crud::PAGE_DETAIL/Crud::PAGE_EDIT),
        LinkField::TARGET => '_blank'/'_self',
        LinkField::PAGE_NAME => $pageName
    ]
);
```

NOTE: Works also with ->autocomplete() fields. But only works for "ToOne" relations!

```php
yield LinkField::link(AssociationField::new('person', 'Person')
    ->autocomplete(), [
        LinkField::URL => $this->adminUrlGenerator->setController(SomeCrudController::class)->setAction(Crud::PAGE_DETAIL/Crud::PAGE_EDIT),
        LinkField::TARGET => '_blank'/'_self',
        LinkField::PAGE_NAME => $pageName
    ]
);
```

NOTE: If you want link to follow your actual Crud Action, then don't manually do ->setAction(), it will automatically set Action to your current action.

```php
public function configureCrud(Crud $crud): Crud
{
    $crud = parent::configureCrud($crud);

    TreeConfigurator::applyTreeLayout($crud);

    return $crud;
}
```

## ⚙️ Stimulus Controllers

The bundle provides four native controllers:

| Controller  | Description                                                |
|-------------|------------------------------------------------------------|
| `dependent` | Handles asynchronous loading of dependent field options    |
| `locked`    | Manages field unlocking with confirmation alerts           |
| `embed`     | Resize iframe to its content height                        |
| `tree`      | Generates wonderful tree hierarchy with drag 'n drop       |
| `link`      | Possibility to redirect to ToOne relation edit/detail page |

If you're using AssetMapper, they’re automatically registered.
Otherwise, you can manually import them from `@iamczech/easyadmin-fields`.

---

## 🌟 Feature Roadmap

### ✅ Current Features

* **DependentField**: dynamic dependency management between fields
* **EmbedField**: rendering crud controllers within another crud controller with a custom popup for a better UX and consistent styling inside EasyAdmin.
* **LockedTextField**: controlled unlocking of grouped inputs via confirmation
* **LinkField**: possibility to redirect to ToOne relation edit/detail page
* **TreeConfigurator**: controlled unlocking of grouped inputs via confirmation

### 🧠 Upcoming Features

* 🌲 **TreeField**
  Visual tree hierarchy for `Gedmo\Tree` entities on the index page (collapsible nodes + drag & drop planned).

---

## 🧪 Development Setup

Clone and link locally if developing:

```bash
git clone https://github.com/iamczech/easyadmin-fields-bundle.git
cd easyadmin-fields-bundle

# Build controllers for distribution
npm install
npm run build
```

Then update your Symfony project:

```bash
php bin/console importmap:install
php bin/console asset-map:compile
```

---

## 🤝 Contributing

Pull requests, bug reports and feature ideas are very welcome!
Follow PSR-12 coding style and use ES modules for front-end logic.

---

## 🧪 License

This bundle is open-sourced software licensed under the **MIT License**.
Copyright © [iamczech](https://github.com/iamczech)

---

## 🦯 Summary

| Feature                   | Description                                       |
| ------------------------- |---------------------------------------------------|
| **Symfony Compatibility** | Symfony 7.4 and newer                             |
| **PHP Version**           | PHP 8.4+                                          |
| **Asset Pipeline**        | Fully compatible with AssetMapper                 |
| **Provided Fields**       | `DependentField`, `LockedTextField`, `EmbedField` |
| **Upcoming**              | `TreeField`, enhanced Autocomplete                |

---

> 💡 *iamczech/easyadmin-fields-bundle* — bringing modern, reactive and elegant form fields to EasyAdmin ✨

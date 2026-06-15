<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use Symfony\Component\Form\CallbackTransformer;


class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureActions(Actions $actions): Actions
        {
            return $actions
                // atjauno paramtetrus
                ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                    return $action
                        ->setLabel('Pievienot preci (Add Product)') // Text
                        ->setIcon('fa fa-plus')                     // Icon
                        ->setCssClass('btn btn-primary');           // Bootstrap
                })
                
                
                ->add(Crud::PAGE_EDIT, Action::INDEX);
        }
    public function configureFields(string $pageName): iterable
    {
        $attributesField = CodeEditorField::new('attributes', 'Specifikācijas (JSON)')
            ->setLanguage('javascript')
            ->setHelp('Piemērs: {"cores": "6", "socket": "AM5"}')
            ->hideOnIndex();

        $attributesField->setFormTypeOption('getter', function (Product $product, $form): string {
            $arrayValue = $product->getAttributes();
            if (empty($arrayValue)) {
                return "{\n  \n}";
            }
            return json_encode($arrayValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        });

        $attributesField->setFormTypeOption('setter', function (Product $product, ?string $stringValue, $form): void {
            if (empty($stringValue)) {
                $product->setAttributes([]);
                return;
            }
            $decoded = json_decode($stringValue, true);
            $product->setAttributes(is_array($decoded) ? $decoded : []);
        });
        return [
            //   ID 
            IdField::new('id')->hideOnForm(),

            //  info
            TextField::new('name', 'Preces nosaukums (Name)'),
            
            TextField::new('uniqueCode', 'Unikālais kods (Part Number / SKU)'),
            
            TextEditorField::new('description', 'Apraksts (Description)'),

            // 3. Kategorija
            AssociationField::new('category', 'Kategorija (Category)'),

            // 4. cena
            MoneyField::new('price', 'Cena (Price)')
                ->setCurrency('EUR')
                ->setStoredAsCents(false), 
                
            IntegerField::new('stock', 'Skaits noliktavā (Stock)'),

            // 5. attaels
            ImageField::new('image', 'Galvenais attēls (Main Image)')
                ->setBasePath('uploads/images')
                ->setUploadDir('public/uploads/images')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
 
            TextField::new('media', 'Papildus media (Media links/paths)'),

            $attributesField,
        ];
    }
}
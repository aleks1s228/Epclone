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

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureActions(Actions $actions): Actions
        {
            return $actions
                // Обновляем параметры уже существующего встроенного действия создания товара
                ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                    return $action
                        ->setLabel('Pievienot preci (Add Product)') // Текст кнопки
                        ->setIcon('fa fa-plus')                     // Иконка
                        ->setCssClass('btn btn-primary');           // Стиль синей кнопки Bootstrap
                })
                
                // На странице редактирования возвращаем кнопку возврата к списку
                ->add(Crud::PAGE_EDIT, Action::INDEX);
        }
    public function configureFields(string $pageName): iterable
    {
        return [
            // 1. Системный ID (скрыт в форме создания)
            IdField::new('id')->hideOnForm(),

            // 2. Основная информация о железе
            TextField::new('name', 'Preces nosaukums (Name)'),
            
            TextField::new('uniqueCode', 'Unikālais kods (Part Number / SKU)'),
            
            TextEditorField::new('description', 'Apraksts (Description)'),

            // 3. Категория (Связь ManyToOne)
            AssociationField::new('category', 'Kategorija (Category)'),

            // 4. Коммерческие данные
            MoneyField::new('price', 'Cena (Price)')
                ->setCurrency('EUR')
                ->setStoredAsCents(false), // Так как в БД тип DECIMAL(10,2), а не центы в Integer
                
            IntegerField::new('stock', 'Skaits noliktavā (Stock)'),

            // 5. Изображения и медиа-ресурсы
            // Для главного изображения используем ImageField. Файлы будут загружаться в public/uploads/images
            ImageField::new('image', 'Galvenais attēls (Main Image)')
                ->setBasePath('uploads/images')
                ->setUploadDir('public/uploads/images')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),

            // Поле media типа TEXT (может использоваться для ссылок на видео/инструкции или дополнительные пути)
            TextField::new('media', 'Papildus media (Media links/paths)'),

            // 6. Спецификации железа (Тип array в PostgreSQL/MySQL)
            // Позволяет добавлять кастомные характеристики комплектующих (например: Сокет, TDP, Частота)
            ArrayField::new('attributes', 'Specifikācijas / Atribūti (Attributes)'),
        ];
    }
}
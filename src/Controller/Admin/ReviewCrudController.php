<?php

namespace App\Controller\Admin;

use App\Entity\Review;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class ReviewCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Review::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Atsauksme')
            ->setEntityLabelInPlural('Atsauksmes')
            ->setDefaultSort(['createdAt' => 'DESC']); 
    }

    public function configureFields(string $pageName): iterable
        {
            yield IdField::new('id')->hideOnForm();
            

            yield AssociationField::new('product', 'Prece')
                ->setRequired(true);
                
            yield AssociationField::new('user', 'Lietotājs')
                ->setRequired(true);

            yield IntegerField::new('rating', 'Vērtējums (1-5)')
                ->setFormTypeOptions([
                    'attr' => [
                        'min' => 1, 
                        'max' => 5,
                        'step' => 1
                    ]
                ]);

            yield TextareaField::new('description', 'Komentārs');

            yield ChoiceField::new('statuss', 'Statuss')
                ->setChoices([
                    'Jauns (Gaidat moderāciju)' => 'new',
                    'Apstiprināts' => 'approved',
                    'Noraidīts' => 'rejected'
                ]);

            yield DateTimeField::new('createdAt', 'Izveidots')
                ->hideOnForm();
        }
}
<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                "label" => "Nom du produit: "
                ])
            ->add('reference', TextType::class, [
                "label" => "Réference: "
            ])
            ->add('quantity', IntegerType::class, ["label" => "Quantité: "], [
                "attr" => ["min" => 0, "max"=> 100]
            ])
            ->add('name_img', FileType::class, [
                "required" => false, 'empty_data' => null, "label" => "image (*facultatif): "
            ])
            ->add('emplacement', TextType::class, [
                "label" => "Emplacement au rack: "
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('from_account_id', NumberType::class, [
                'label' => 'Compte source',
                'required' => true,
                'attr' => ['class' => 'form-control input-field'],
            ])
            ->add('to_account_id', NumberType::class, [
                'label' => 'Compte destinataire',
                'required' => true,
                'attr' => ['class' => 'form-control input-field'],
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Montant',
                'required' => true,
                'scale' => 2,
                'attr' => ['class' => 'form-control input-field'],
            ])
            ->add('label', TextType::class, [
                'label' => 'Libellé',
                'required' => false,
                'attr' => ['class' => 'form-control input-field'],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de transaction',
                'choices' => [
                    'Virement' => 'virement',
                    'Dépôt' => 'depot',
                    'Retrait' => 'retrait',
                ],
                'required' => true,
                'attr' => ['class' => 'form-control input-field'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Effectuer le virement',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}

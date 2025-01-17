<?php

namespace App\Form;

use App\Entity\BankAccount;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fromAccount', EntityType::class, [
                'label' => 'Compte',
                'class' => BankAccount::class,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('ba')
                        ->where('ba.Users = :user')
                        ->setParameter('user', $options['user']);
                },
                'choice_label' => 'Name',
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
            ->add('submit', SubmitType::class, [
                'label' => 'Effectuer le virement',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user' => null,
        ]);
    }
}

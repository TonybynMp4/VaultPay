<?php
namespace App\Form;

use App\Entity\BankAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class BankAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du compte',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de compte',
                'choices' => [
                    'Courant' => 0,
                    'Epargne' => 1,
                ],
            ])
            ->add('balance', MoneyType::class, [
                'label' => 'Montant initial',
                'currency' => 'EUR',
                'constraints' => [
                    new Assert\Positive(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BankAccount::class,
        ]);
    }
}

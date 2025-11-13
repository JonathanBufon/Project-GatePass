<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulário de Perfil do Cliente (DTO não mapeado diretamente a entidades).
 */
class PerfilFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control', 'placeholder' => 'seu-email@exemplo.com']
            ])
            ->add('nomeCompleto', TextType::class, [
                'label' => 'Nome Completo',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Seu nome completo']
            ])
            ->add('cpf', TextType::class, [
                'label' => 'CPF',
                'attr' => ['class' => 'form-control', 'placeholder' => '000.000.000-00']
            ])
            ->add('salvar', SubmitType::class, [
                'label' => 'Salvar Alterações',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}

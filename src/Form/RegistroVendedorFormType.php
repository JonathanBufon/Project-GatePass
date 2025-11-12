<?php

namespace App\Form;

use App\Dto\RegistroVendedorDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Define o formulário de registro do Vendedor (DTO virtual).
 * Não está vinculado a nenhuma entidade (data_class = null).
 * @author Jonathan Bufon
 */
class RegistroVendedorFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email de Acesso',
                'attr' => ['placeholder' => 'seu-email@exemplo.com', 'class' => 'form-control'],
                'constraints' => [new NotBlank(['message' => 'Por favor, insira um e-mail.'])],
            ])
            ->add('tipoDocumento', ChoiceType::class, [
                'label' => 'Tipo de Documento',
                'choices' => [
                    'Pessoa Física (CPF)' => 'cpf',
                    'Pessoa Jurídica (CNPJ)' => 'cnpj',
                ],
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Selecione...',
                'required' => true,
                'attr' => ['class' => 'form-select', 'data-mask-selector' => 'tipo'],
                'constraints' => [new NotBlank(['message' => 'Selecione o tipo de documento.'])],
            ])
            ->add('documento', TextType::class, [
                'label' => 'CPF/CNPJ',
                'attr' => ['placeholder' => '000.000.000-00 ou 00.000.000/0001-00', 'class' => 'form-control', 'inputmode' => 'numeric', 'data-mask' => 'document'],
                'constraints' => [
                    new NotBlank(['message' => 'Informe o documento.']),
                    new Length(['min' => 11, 'max' => 18]),
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        $form = $context->getRoot();
                        $tipo = $form->get('tipoDocumento')->getData();
                        if (!$value || !$tipo) { return; }
                        $digits = preg_replace('/\D+/', '', (string) $value);
                        if ($tipo === 'cpf') {
                            if (strlen($digits) !== 11 || !self::isValidCpf($digits)) {
                                $context->buildViolation('CPF inválido.')->addViolation();
                            }
                        } elseif ($tipo === 'cnpj') {
                            if (strlen($digits) !== 14 || !self::isValidCnpj($digits)) {
                                $context->buildViolation('CNPJ inválido.')->addViolation();
                            }
                        }
                    })
                ],
            ])
            ->add('nomeFantasia', TextType::class, [
                'label' => 'Nome Fantasia da Empresa',
                'attr' => ['placeholder' => 'Nome da sua produtora', 'class' => 'form-control'],
                'constraints' => [new NotBlank(['message' => 'O nome é obrigatório.'])],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Senha',
                    'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'Confirmar Senha',
                    'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'Os campos de senha devem corresponder.',
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, insira uma senha.']),
                    new Length(['min' => 8, 'minMessage' => 'Sua senha deve ter pelo menos {{ limit }} caracteres.']),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Li e aceito os termos de uso (Vendedor)',
                'mapped' => false,
                'attr' => ['class' => 'form-check-input'],
                'constraints' => [new IsTrue(['message' => 'Você deve aceitar nossos termos.'])],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistroVendedorDto::class, // SRP: É um DTO
        ]);
    }

    // Algoritmos simples de validação CPF/CNPJ (estático no Type para manter local)
    private static function isValidCpf(string $cpf): bool
    {
        if (preg_match('/^(\d)\1{10}$/', $cpf)) { return false; }
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += (int)$cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int)$cpf[$t] !== $d) { return false; }
        }
        return true;
    }

    private static function isValidCnpj(string $cnpj): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) { return false; }
        $lengths = [12, 13];
        $weights1 = [5,4,3,2,9,8,7,6,5,4,3,2];
        $weights2 = [6,5,4,3,2,9,8,7,6,5,4,3,2];
        $calc = function($base, $weights) {
            $sum = 0; for ($i=0; $i<count($weights); $i++) { $sum += (int)$base[$i] * $weights[$i]; }
            $rest = $sum % 11; $dv = ($rest < 2) ? 0 : 11 - $rest; return $dv;
        };
        $base = substr($cnpj, 0, 12);
        $dv1 = $calc($base, $weights1);
        $base .= (string)$dv1;
        $dv2 = $calc($base, $weights2);
        return $cnpj[12] == (string)$dv1 && $cnpj[13] == (string)$dv2;
    }
}
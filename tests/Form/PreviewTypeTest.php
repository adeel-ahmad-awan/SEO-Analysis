<?php

namespace App\Tests\Form;

use App\Form\PreviewType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class PreviewTypeTest extends TypeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    protected function getTestedType(): string
    {
        return PreviewType::class;
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'url' => 'https://www.example.com',
        ];

        $form = $this->factory->create($this->getTestedType());
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($formData, $form->getData());
    }

    public function testSubmitInvalidData(): void
    {
        $formData = [
            'url' => 'invalid_url',
        ];

        $form = $this->factory->create($this->getTestedType());
        $form->submit($formData);

        $this->assertFalse($form->isValid());
        $this->assertGreaterThanOrEqual(1, $form->getErrors(true)->count());
    }
}
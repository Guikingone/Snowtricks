<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Unit\Forms;

use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Test\TypeTestCase;
use AppBundle\Form\Type\TricksType;
use AppBundle\Entity\Tricks;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Class TricksFormsTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksFormsTest extends TypeTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $validators = $this->getMock('Symfony\Component\Validator\Validator\ValidatorInterface');
        $validators->method('validate')->will($this->returnValue(new ConstraintViolationList()));
        $formTypeExtension = new FormTypeValidatorExtension($validators);
        $coreExtension = new CoreExtension();

        $this->factory = Forms::createFormFactoryBuilder()
                              ->addExtensions($this->getExtensions())
                              ->addExtension($coreExtension)
                              ->addTypeExtension($formTypeExtension)
                              ->getFormFactory();
    }

    /**
     * Test if data's can be passed through the form.
     */
    public function testSubmitData()
    {
        $entity = new Tricks();
        $entity->setName('Backflip');
        $entity->setResume('A simple resume !');
        $entity->setGroups('Flip');

        // Transform the entity in a array.
        $data = (array) $entity;

        $form = $this->factory->create(TricksType::class);
        $form->submit($data);

        $this->assertTrue($form->isSubmitted());
        $this->assertEquals($data, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($data) as $key) {
            $this->assertArrayHasKey($key, $children);
        }

        $this->assertEquals('app_bundle_tricks_type', $form->getName());
    }
}

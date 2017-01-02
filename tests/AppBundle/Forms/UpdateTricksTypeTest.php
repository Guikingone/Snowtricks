<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Forms;

use Symfony\Component\Form\Test\TypeTestCase;
use AppBundle\Form\Type\UpdateTricksType;
use AppBundle\Entity\Tricks;

/**
 * Class UpdateTricksTypeTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UpdateTricksTypeTest extends TypeTestCase
{
    /**
     * Test if data's can be passed through the form.
     */
    public function testSubmitData()
    {
        $data = [
            'name' => 'Backflip',
            'group' => 'Flip',
            'content' => 'A simple content',
        ];

        $form = $this->factory->create(UpdateTricksType::class);

        $instance = Tricks::fromArray($data);
        $form->submit($instance);

        $this->assertTrue($form->isSubmitted());
        $this->assertEquals($instance, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($data) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
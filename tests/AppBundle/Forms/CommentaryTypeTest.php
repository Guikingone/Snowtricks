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
use AppBundle\Form\Type\CommentaryType;
use AppBundle\Entity\Commentary;

/**
 * Class CommentaryTypeTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class CommentaryTypeTest extends TypeTestCase
{
    /**
     * Test if data's can be passed through the form.
     */
    public function testSubmitData()
    {
        $entity = new Commentary();
        $entity->setContent('A simple comment !');
        $entity->setPublicationDate(new \DateTime());

        $data = [
            'content' => $entity->getContent(),
            'publicationDate' => $entity->getPublicationDate(),
        ];

        $form = $this->factory->create(CommentaryType::class, $entity);
        $form->submit($entity);

        $this->assertTrue($form->isSubmitted());
        $this->assertEquals($data, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($data) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}

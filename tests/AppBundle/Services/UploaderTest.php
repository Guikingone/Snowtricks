<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Services;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

// Service
use AppBundle\Services\Uploader;

/**
 * Class UploaderTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UploaderTest extends KernelTestCase
{
    /** {@inheritdoc} */
    public function setUp()
    {
        static::bootKernel();
    }

    /**
     * Test if the service can be found.
     */
    public function testIfServiceIsFound()
    {
        $uploader = static::$kernel->getContainer()
                                   ->get('app.uploader');

        $this->assertInstanceOf(Uploader::class, $uploader);
    }

    /**
     * Test if the image can be uploader and moved using the uploader.
     *
     * @see Uploader::uploadFile()
     */
    public function testIfFileCanBeUploaded()
    {
        $uploader = static::$kernel->getContainer()
                                   ->get('app.uploader');

        // Stock the web folder path.
        $webFolder = static::$kernel->getContainer()
                                    ->getParameter('kernel.root_dir');

        if ($uploader instanceof Uploader) {
            $file = new UploadedFile(
                $webFolder.'/../web/images/backflip.jpg',
                'backflip.jpg'
            );
            $uploader->uploadFile($file);
        }
    }
}

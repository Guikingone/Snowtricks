<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Uploader.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class Uploader
{
    // Store the image.uploads.dir
    private $imageDir;

    /**
     * Uploader constructor.
     *
     * @param $imageDir
     */
    public function __construct($imageDir)
    {
        $this->imageDir = $imageDir;
    }

    /**
     * Allow to upload a file and save it into the images.uploads.dir.
     *
     * @param UploadedFile $file
     *
     * @throws FileException
     *
     * @return string
     */
    public function uploadFile(UploadedFile $file)
    {
        $name = md5(uniqid('image_', false)).'.'.$file->getExtension();

        $file->move($this->imageDir, $name);

        return $name;
    }
}

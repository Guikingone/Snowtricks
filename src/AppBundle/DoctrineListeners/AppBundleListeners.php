<?php
/**
 * Created by PhpStorm.
 * User: Guillaume
 * Date: 24/01/2017
 * Time: 15:35.
 */

namespace AppBundle\DoctrineListeners;

use AppBundle\Entity\Tricks;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

class AppBundleListeners
{
    private $imageDir;

    /**
     * AppBundleListeners constructor.
     *
     * @param $imageDir
     */
    public function __construct($imageDir)
    {
        $this->imageDir = $imageDir;
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws FileNotFoundException
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Tricks) {
            return;
        }

        // Only if the entity has store files.
        if ($entity->getImages()) {
            $filename = $entity->getImages();

            foreach ($filename as $file) {
                $entity->addImage(new File($this->imageDir.'/'.$file));
            }
        }
    }
}

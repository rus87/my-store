<?php

namespace AppBundle\Utils;

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PhotoManager
{
    private $filterManager;
    private $dataManager;
    private $kernelRoot;
    private $cacheManager;
    private $entityManager;

    /**
     * PhotoManager constructor.
     * @param EntityManager $entityManager
     * @param FilterManager $filterManager
     * @param DataManager $dataManager
     * @param CacheManager $cacheManager
     * @param $kernelRoot
     */
    public function __construct(EntityManager $entityManager, FilterManager $filterManager, DataManager $dataManager, CacheManager $cacheManager, $kernelRoot)
    {
        $this->filterManager = $filterManager;
        $this->dataManager = $dataManager;
        $this->kernelRoot = $kernelRoot;
        $this->cacheManager = $cacheManager;
        $this->entityManager = $entityManager;
    }

    public function downscale($relPath)
    {
        $imageBinary = $this->dataManager->find('resize_upload', $relPath);
        $resizedImageBinary = $this->filterManager->applyFilter($imageBinary, 'resize_upload');
        $f = fopen($this->kernelRoot.'/../web/'.$relPath, 'w');
        return fwrite($f, $resizedImageBinary->getContent());
    }

    public function remove($id)
    {
        $photoRepo = $this->entityManager->getRepository('AppBundle:Photo');
        $productRepo = $this->entityManager->getRepository('AppBundle:Product');
        $photo = $photoRepo->find($id);
        if(! $photo)
            throw new NotFoundHttpException('Photo with id='.$id.' not found');
        $product = $photo->getProduct();
        if(! $product){
            $product = $productRepo->findOneBy(['mainPhoto1' => $photo->getId()]);
            if($product){
                $this->cacheManager->remove($product->getMainPhoto1Path());
                unlink($product->getMainPhoto1Path());
                $product->setMainPhoto1(null);
            }
            else{
                $product = $productRepo->findOneBy(['mainPhoto2' => $photo->getId()]);
                if($product){
                    $this->cacheManager->remove($product->getMainPhoto2Path());
                    unlink($product->getMainPhoto2Path());
                    $product->setMainPhoto2(null);
                }
            }
        }
        else{
            $this->cacheManager->remove($photo->getPath());
            unlink($photo->getPath());
        }
        $this->entityManager->remove($photo);
        $this->entityManager->flush();
    }
}
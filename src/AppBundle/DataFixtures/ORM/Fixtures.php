<?php
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Brand;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use AppBundle\Entity\Products\Jacket;
use AppBundle\Entity\Products\Sweater;
use AppBundle\Entity\Products\Trousers;
use AppBundle\Entity\Products\Blouse;
use AppBundle\Utils\ProductManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

class LoadFixtures implements FixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $om)
    {
        $this->setRandomMainPhotosToAll('/home/rus/MY', $om);
    }


    private function setRandomMainPhotosToAll($photosDir, ObjectManager $om)
    {
        $productManager = $this->container->get('product_manager');
        $productRepo = $om->getRepository('AppBundle:Product');

        //$products = $productRepo->findBy([], null, 100, 1000);
        $products = $this->getProductsWithSmallPhoto($om);
        foreach($products as &$product){
            $outStr = '';
            $file = $this->getFile($photosDir);
            $productManager->setMainPhoto($product->getId(), $file[0], 1);
            $outStr .= $product->getId().': 1 => '.$file[1];
            $file = $this->getFile($photosDir);
            $productManager->setMainPhoto($product->getId(), $file[0], 2);
            $outStr .= ', 2 => '.$file[1]."\n";
            echo $outStr;
        }
    }

    private function getFile($photosDir)
    {
        $fileNames = scandir($photosDir);
        unset($fileNames[0]);
        unset($fileNames[1]);
        $file = null;
        $name = null;
        do $name = $fileNames[array_rand($fileNames)];
        while ($name == 'used');
        copy($photosDir.'/'.$name, $photosDir.'/used/'.$name);

        return [new File($photosDir.'/used/'.$name), $name];
    }

    private function getProductsWithSmallPhoto(ObjectManager $objectManager)
    {
        $productsWithSmallPhoto2 = [];
        $products = $objectManager->getRepository('AppBundle:Product')->findAll();
        foreach($products as $product){
            $photoParams = getimagesize($this->container->get('kernel')->getRootDir().'/../web/'.$product->getMainPhoto2Path());
            if($photoParams[1] < 740)
                $productsWithSmallPhoto2[] = $product;
        }
        return $productsWithSmallPhoto2;
    }

    private function setCategoryBrand(ObjectManager $manager)
    {
        $productRepo = $manager->getRepository('AppBundle:Product');
        $brandRepo = $manager->getRepository('AppBundle:Brand');
        $catRepo = $manager->getRepository('AppBundle:Category');
        $products = $productRepo->findAll();
        $handledCats = [];
        foreach($products as $product){
            $category = $product->getCategory();
            if(in_array($category->getId(), $handledCats))
                continue;
            $brand = $product->getBrand();
            $category->addBrand($brand);
            $handledCats[] = $category->getId();
            $manager->persist($category);
        }
        $manager->flush();
    }

    private function loadProducts(ObjectManager $manager)
    {
        $categories = $manager->getRepository('AppBundle:Category')->findAll();
        $productRepo = $manager->getRepository('AppBundle:Product');
        $catRepo = $manager->getRepository('AppBundle:Category');
        $classNames = $productRepo::PRODUCTS_CLASSES;

        $genders = ['male', 'female'];
        $brands = ['LNA', 'Baldinini', 'Mad Rock', 'Acorn', 'Kanzler', 'Melissa Odabash', 'Canali', 'Preen', 'Tommy Hilfiger', 'Versus'];
        $waists = ['55', '64', '71', '60', '67'];
        for($i=0; $i<=500; $i++)
        {
            $productClassName = $classNames[array_rand($classNames)];
            $class = 'AppBundle\Entity\Products\\'.$productClassName;
            $product = new $class;

            $product->setDescription("test description for this test product");
            $product->setTitle("product $i");
            $product->setPrice(rand(5, 30));
            $product->setGender($genders[array_rand($genders)]);
            $product->setBrand($brands[array_rand($brands)]);
            if(property_exists($product, 'sleeveLength'))
                $product->setSleeveLength([44,45,46,47,48,49,50,51,51,52][array_rand([44,45,46,47,48,49,50,51,51,52])]);
            if(property_exists($product, 'filling'))
                $product->setFilling('test filling');
            if(property_exists($product, 'outerMaterial'))
                $product->setOuterMaterial('material test');
            if(property_exists($product, 'seasonality'))
                $product->setSeasonality('seasonality testing value');
            if(property_exists($product, 'composition'))
                $product->setComposition('material test');
            if(property_exists($product, 'waist'))
                $product->setWaist($waists[array_rand($waists)]);
            if(property_exists($product, 'length'))
                $product->setLength('97');

            $classCats = $catRepo->getClassCategories($productClassName);
            $category = $classCats[array_rand($classCats)];
            $product->setCategory($category);
            $manager->persist($product);
        }
        $manager->flush();
    }

    private function loadBrands(ObjectManager $manager)
    {
        $prodRepo = $manager->getRepository('AppBundle:Product');
        $catRepo = $manager->getRepository('AppBundle:Category');
        $brandRepo = $manager->getRepository('AppBundle:Brand');
        $categories = $catRepo->findAll();
        /*$brand = new Brand('LNA');
        $brand->addCategory($categories[array_rand($categories)]);
        $brand->addCategory($categories[array_rand($categories)]);
        $manager->persist($brand);
        $manager->flush();*/

        $product = $prodRepo->find(120);
        //$brand = $brandRepo->find(2);
        $product->setBrand();
        $manager->flush();
    }


}

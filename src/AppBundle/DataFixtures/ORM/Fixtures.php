<?php
namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use AppBundle\Entity\Products\Jacket;
use AppBundle\Entity\Products\Sweater;
use AppBundle\Entity\Products\Trousers;
use AppBundle\Entity\Products\Blouse;

class LoadFixtures implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $categories = $manager->getRepository('AppBundle:Category')->findAll();
        $productRepo = $manager->getRepository('AppBundle:Product');
        $catRepo = $manager->getRepository('AppBundle:Category');
        $classNames = $productRepo::PRODUCTS_CLASSES;

        $genders = ['male', 'female'];
        $brands = ['LNA', 'Baldinini', 'Mad Rock', 'Acorn', 'Kanzler', 'Melissa Odabash', 'Canali', 'Preen', 'Tommy Hilfiger', 'Versus'];
        $waists = ['55', '64', '71', '60', '67'];
        for($i=0; $i<=100; $i++)
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
                $product->setSleeveLength('48');
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


/*
    private function loadCategories(ObjectManager $manager)
    {
        $jacket = new Category();
        $jacket->setName("Jacket");

        $trousers = new Category();
        $trousers->setName("Trousers");

        $sweater = new Category();
        $sweater->setName("Sweater");

        $manager->persist($jacket);
        $manager->persist($trousers);
        $manager->persist($sweater);

        $manager->flush();
    }

    private function loadProducts(ObjectManager $manager)
    {

        $seasons = ["winter", "spring", "summer", "autumn"];
        $genders = ["male", "female"];
        $categories = ["Trousers", "Jacket", "Sweater"];

        foreach(range(1, 30) as $i)
        {

            $product = new Product();
            $product->setDescription("test description for this test product");
            $product->setTitle("product $i");
            $product->setSeason($seasons[array_rand($seasons)]);
            $product->setPrice(rand(120, 460));
            $product->setGender($genders[array_rand($genders)]);

            $manager->persist($product);
            $manager->flush();
        }
    }
*/
}

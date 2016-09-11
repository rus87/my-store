<?php
namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Category;
use AppBundle\Entity\Product;

class LoadFixtures implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $this->loadProducts($manager);
    }

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

}

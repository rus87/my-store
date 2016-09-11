<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Event\MyEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Product;
use AppBundle\Entity\Category;

class HomeController extends Controller
{

    /**
     * @Route(path="/")
     */
    public function homeAction()
    {
        return $this->render("mybase.html.twig");
    }
    /**
     * @Route(path="/pull/{productId}", requirements = {"productId" : "\d+"})
     */
    public function pullAction($productId)
    {
        $product = $this->getDoctrine()->getManager()->getRepository("AppBundle:Product")->findOneById($productId);
        if (!$product)
            throw $this->createNotFoundException('Нет продукта с идом '.$productId);
        $this->get('cartManager')->pullProduct($product);
        $products = $this->getDoctrine()->getRepository("AppBundle:Product")->findAll();
        return $this->render("Home/index.html.twig", ['products' => $products]);
    }

    /**
     * @Route(path = "/test")
     */
    public function testAction()
    {
        dump($this->get("cartManager")->getProducts());
        $dispatcher = $this->get("event_dispatcher");
        $myEvent = new MyEvent("input data for event");
        $dispatcher->dispatch("my_event", $myEvent);
        dump($this->getDoctrine()->getRepository("AppBundle:Product")->test());
        return $this->render("mybase.html.twig");
    }

    /**
     * @Route(path = "/add")
     */
    public function addAction()
    {
        $product = new Product();
        $cat = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findOneBy(['name' => 'Sweater']);
        $product->setTitle("test");
        $product->setPrice(33);
        $product->setGender('male');
        $product->setSeason('summer');
        $product->setCategory($cat);
        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();
        return $this->render("mybase.html.twig");
    }


}
<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Brand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Product;
use AppBundle\Entity\Category;
use AppBundle\Form\Admin\ProductType;
use AppBundle\Form\Admin\BrandType;

class BrandCrudController extends Controller
{
    /**
     * @Route(path="/admin/brand/add")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createBrandAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $catsRepo = $em->getRepository('AppBundle:Category');
        $cats = $catsRepo->findAll();
        foreach($cats as $cat)
            $catsChoice [$cat->getName()]= $cat->getName();
        $brand = new Brand();
        $form = $this->createForm(BrandType::class, $brand);
        $form->handleRequest($request);
        if($form->isValid()){
            dump($brand);
            $em->persist($brand);
            $em->flush();
        }
        return $this->render('Admin/BrandAdd.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route(path="/admin/brand/test")
     */
    public function testAction()
    {
        $em = $this->getDoctrine()->getManager();
        $catsRepo = $em->getRepository('AppBundle:Category');
        $prodManager = $this->get('product_manager');
        $allCats = $catsRepo->findAll();
        foreach($allCats as $cat){
            $products = $prodManager->findByCategory($cat->getName());
            foreach($products as $product){
                $brand = $product->getBrand();
                $prodCat = $product->getCategory();
                if(! $brand->getCategories()->contains($prodCat)){
                    $brand->addCategory($prodCat);
                    $em->persist($brand);
                    $em->flush();
                }
            }
        }
        return $this->render('Admin/AdminBase.html.twig');
    }
}
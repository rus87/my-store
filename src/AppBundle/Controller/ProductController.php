<?php

namespace AppBundle\Controller;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Category;
use AppBundle\Utils\CrumbsGenerator\InputData;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends BaseController
{

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route(
     *      path="/product/show/{id}",
     *      requirements={"id" : "\d+"},
     *      options={"expose" : "true"}
     * )
     */
    public function showAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $prodRepo = $em->getRepository('AppBundle:Product');
        $product = $prodRepo->find($id);
        dump(explode('\\', get_class($product))[count(explode('\\', get_class($product)))-1]);
        $gender = $product->getGender();
        $currency = $this->get('currency_manager')->getClientCurrency();
        if(!$product) throw $this->createNotFoundException("No such product.");
        $templateData['categories'] = $this->getDoctrine()->getManager()
            ->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        $parentCats = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")
            ->getAllParents($product->getCategory()->getName());
        $crumbsData = [new InputData('app_home_home')];
        if($parentCats){
            foreach($parentCats as $parentCat)
                $crumbsData []= new InputData('app_products_showbycategory', ['categoryName' => $parentCat->getName()]);
        }
        $crumbsData[] = new InputData('app_products_showbycategory', ['categoryName'=>$product->getCategory()->getName()]);
        $crumbsData[] = new InputData('app_product_show', null, $product->getTitle());
        $crumbs = $this->get('app.crumbs_generator')->make($crumbsData);
        $templateData['currency'] = $currency;
        $this->setProductsCurrency([$product], $currency);
        $templateData['product'] = $product;
        $templateData['title'] = $product->getTitle();
        $templateData['crumbs'] = $crumbs;
        $templateData['randomProducts'] = [];
        foreach([1,2,3] as $i)
            $templateData['randomProducts'] []= $this->getDoctrine()->getManager()->getRepository("AppBundle:Product")
                ->getRandom($product->getCategory());
        $this->setProductsCurrency($templateData['randomProducts'], $currency);
        $productClass = explode('\\', get_class($product))[count(explode('\\', get_class($product)))-1];
        $templateData['form'] = $this->handleCurrencyForm($request, 'app_product_show', ['id' => $id]);
        if($this->currencyRedirectResponse) return $this->currencyRedirectResponse;
        $templateData['searchForm'] = $this->handleSearchForm($request);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;
        return $this->render("Product/$productClass.html.twig", $templateData);
    }




}
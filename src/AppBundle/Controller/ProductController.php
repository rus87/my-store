<?php

namespace AppBundle\Controller;

use AppBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Category;
use AppBundle\Utils\CrumbsGenerator\InputData;

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
    public function showAction($id)
    {
        $product = $this->getDoctrine()->getManager()->getRepository("AppBundle:Product")->find($id);
        $gender = $product->getGender();
        $currency = $this->get('currency_manager')->getClientCurrency();
        if(!$product) throw $this->createNotFoundException("No such product.");
        $templateData['categories'] = $this->getDoctrine()->getManager()
            ->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        $parentCats = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")
            ->getAllParents($product->getCategory()->getName());
        $crumbsData = [
            new InputData('app_home_home'),
            new InputData('app_products_showbygender', ['gender' => $gender]),];
        if($parentCats){
            foreach($parentCats as $parentCat)
                $crumbsData []= new InputData('app_products_showbygenderandcategory',
                    ['gender' => $gender, 'category' => $parentCat->getName()]);
        }
        $crumbsData[] = new InputData('app_products_showbygenderandcategory', ['gender'=>$gender, 'category'=>$product->getCategory()->getName()]);
        $crumbsData[] = new InputData('app_product_show', null, $product->getTitle());
        dump($crumbs = $this->get('app.crumbs_generator')->make($crumbsData));
        $templateData['form'] = $this->createCurrencyForm('app_product_show', ['id' => $id])->createView();
        $templateData['currency'] = $currency;
        $this->setProductsCurrency([$product], $currency);
        $templateData['product'] = $product;
        $templateData['crumbs'] = $crumbs;
        $templateData['randomProducts'] = [];
        foreach([1,2,3] as $i)
            $templateData['randomProducts'] []= $this->getDoctrine()->getManager()->getRepository("AppBundle:Product")
                ->getRandom($product->getCategory());
        $this->setProductsCurrency($templateData['randomProducts'], $currency);

        return $this->render('product/product.html.twig', $templateData);
    }


}
<?php

namespace AppBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;

class CartController extends BaseController
{
    /**
     * @return Response
     * @Route(path="/cart")
     */
    public function showCartAction(Request $request)
    {
        $cartManager = $this->get("cart_manager");
        $templateData['products'] = $cartManager->getCartProducts();
        $templateData['currency'] = $this->get('currency_manager')->getClientCurrency();
        $this->setProductsCurrency($templateData['products'], $templateData['currency']);
        $templateData['categories'] = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")
            ->findBy(['parent' => null]);
        $templateData['form'] = $this->createCurrencyForm('app_cart_showcart', [])->createView();
        $templateData['searchForm'] = $this->handleSearchForm($request);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;
        return $this->render('Cart/cart.html.twig', $templateData);
    }

    /**
     * @param int $productId
     * @Route(
     *      path="/cart/remove/{productId}.{_format}",
     *      requirements = {"productId" : "\d+", "_format" : "html|json"},
     *      options={"expose" : "true"})
     * @return Response|JsonResponse
     */
    public function removeProductAction($productId, $_format)
    {
        $this->get("cart_manager")->removeProduct($productId);

        if($_format == "json") return new JsonResponse($this->getJsonContentWithMiniPhoto());
        elseif($_format == "html") return $this->redirectToRoute('app_cart_showcart');

    }

    /**
     * @Route(
     *      path="/cart/add/{productId}.{_format}",
     *      requirements = {"productId" : "\d+", "_format" : "html|json"},
     *      options={"expose" : "true"})
     * @return Response|JsonResponse
     */
    public function addProductAction($productId, $_format)
    {
        $product = $this->getDoctrine()->getManager()->getRepository("AppBundle:Product")->findOneById($productId);
        if (!$product)
            throw $this->createNotFoundException('Нет продукта с идом '.$productId);
        try{
            $this->get('cart_manager')->pullProduct($product);
        }
        catch(UniqueConstraintViolationException $e){
            return new Response("Already in cart!");
        }
        if($_format == 'html') $this->redirectToRoute('app_cart_showcart');
        else return new JsonResponse($this->getJsonContentWithMiniPhoto());

    }

    /**
     * @return JsonResponse
     * @Route(path="/cart/getproducts", options={"expose" : "true"})
     */
    public function getProductsAction()
    {
        return new JsonResponse($this->getJsonContentWithMiniPhoto());
    }

    /**
     * @return string
     */
    private function getJsonContentWithMiniPhoto()
    {
        $products = $this->get("cart_manager")->getCartProducts();
        if($products)
        {
            $products = $products->getValues();
            $this->setProductsCurrency($products, $this->get('currency_manager')->getClientCurrency());
            foreach ($products as &$product)
            {
                $miniCartPhotoPath = $this->get('liip_imagine.cache.manager')
                    ->getBrowserPath($product->getMainPhoto1Path(), 'mini_cart_thumb');
                $product->setMiniCartPhotoPath($miniCartPhotoPath);
            }
            return $this->get('jms_serializer')
                ->serialize($products, "json", SerializationContext::create()->enableMaxDepthChecks());
        }
        else return '{"products": null}';
    }

}
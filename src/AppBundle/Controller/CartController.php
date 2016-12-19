<?php
namespace AppBundle\Controller;

use AppBundle\Controller\BaseController;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class CartController extends BaseController
{
    /**
     * @param Request $request 
     * @Route(path="/cart")
     *
     * @return Response
     */
    public function showCartAction(Request $request)
    {
        $cartManager = $this->get("cart_manager");
        $templateData['cart'] = $cartManager->getCart();
        $templateData['title'] = 'Cart';
        $templateData['currency'] = $this->get('currency_manager')->getClientCurrency();
        $this->setProductsCurrency(
            $templateData['cart']->getProducts(), 
            $templateData['currency']
        );
        $templateData['categories'] = $this->getDoctrine()->getManager()
            ->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        $templateData['form'] = $this->handleCurrencyForm($request, 'app_cart_showcart');
        if($this->currencyRedirectResponse) {
            return $this->currencyRedirectResponse;
        }
        $templateData['searchForm'] = $this->handleSearchForm($request);
        if($this->searchRedirectResponse) {
            return $this->searchRedirectResponse;
        }
        return $this->render('Cart/cart.html.twig', $templateData);
    }

    /**
     * @param int $productId
     * @param string $_format
     * @Route(
     *      path="/cart/remove/{productId}.{_format}",
     *      requirements = {"productId" : "\d+", "_format" : "html|json"},
     *      options={"expose" : "true"}
     * )
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse|Response
     */
    public function removeProductAction($productId, $_format)
    {
        $product = $this->getDoctrine()->getRepository("AppBundle:Product")->find($productId);
        if (!$product) {
            throw $this->createNotFoundException('Нет продукта с идом '.$productId);
        }
        $this->get("cart_manager")->removeProduct($product);

        if($_format == "json") {
            $response = new JsonResponse($this->getJsonContentWithMiniPhoto());
        } else {
            $response = $this->redirectToRoute('app_cart_showcart');
        }
        return $response;
    }


    /**
     * @param null|int $id
     * @param null|string $action
     * @Route(
     *      path="/cart/update/{action}/{id}",
     *      requirements = {"id":"\d+"},
     *      options={"expose":"true"}
     * )
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse     
     */
    public function updateAction($id = null, $action = null)
    {
        $cartManager = $this->get('cart_manager');
        if ($id) {
            $product = $this->getDoctrine()->getManager()
                ->getRepository("AppBundle:Product")->find($id);
            if (!$product) {
                throw new NotFoundHttpException(sprintf('No product with id=$d', $id));
            }
        } else {
            $action = 'get';
        }

        if ('toggle' == $action) {
            $cart = $cartManager->toggleProduct($product);
        } elseif ('add' == $action) {
            $cart = $cartManager->pullProduct($product);
        } elseif ('remove' == $action) {
            $cart = $cartManager->removeProduct($product);
        } else {
            $cart = $cartManager->getCart();
        }

        if(!$cart->getProducts()->isEmpty())
        {
            $this->setProductsCurrency(
                $cart->getProducts(), 
                $this->get('currency_manager')->getClientCurrency()
            );
            foreach ($cart->getProducts() as &$product) {
                $product->setMiniCartPhotoPath($this->get('liip_imagine.cache.manager')
                    ->getBrowserPath($product->getMainPhoto1Path(), 'mini_cart_thumb'));
                $product->priceDisc = $product->getPrice(true);
            }

            $productsJson = $this->get('jms_serializer')->serialize(
                $cart->getProducts(), 
                'json', 
                SerializationContext::create()->enableMaxDepthChecks()
            );
            $response = new JsonResponse($productsJson);
        } else {
            $response = new JsonResponse('null');
        }
        return $response;
    }

    /**
     * @Route(path="/cart/clear")
     *
     * @return RedirectResponse
     */
    public function clearAction()
    {
        $this->get('cart_manager')->clearCart();
        return $this->redirectToRoute('app_cart_showcart');
    }

    /**
     * @Route(path="/cart/getproducts", options={"expose" : "true"})
     *
     * @return JsonResponse
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
        if($products) {
            $products = $products->getValues();
            $this->setProductsCurrency($products, $this->get('currency_manager')->getClientCurrency());
            foreach ($products as &$product)
            {
                $miniCartPhotoPath = $this->get('liip_imagine.cache.manager')
                    ->getBrowserPath($product->getMainPhoto1Path(), 'mini_cart_thumb');
                $product->setMiniCartPhotoPath($miniCartPhotoPath);
                $product->priceDisc = $product->getPrice(true);
            }
            $output = $this->get('jms_serializer')->serialize(
                $products, 
                "json", 
                SerializationContext::create()->enableMaxDepthChecks()
            );
        }
        else {
            $output = '{"products": null}';
        }
        return $output;
    }
}

<?php


namespace AppBundle\Controller\UserCabinet;


use AppBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use JMS\Serializer\SerializationContext;

class CabinetController extends BaseController
{
    /**
     * @Security("has_role('ROLE_USER')")
     * @Route(path="/cabinet/wishlist")
     */
    public function showWishlistAction(Request $request)
    {
        $product = $this->getDoctrine()->getManager()->getRepository("AppBundle:Product")->find(800);
        //$this->get('user_manager')->toggleProductInWishlist($product);
        $templateData['currency'] = $this->get('currency_manager')->getClientCurrency();
        $templateData['form'] = $this->handleCurrencyForm($request, 'app_usercabinet_cabinet_showwishlist');
        if($this->currencyRedirectResponse) return $this->currencyRedirectResponse;
        $templateData['searchForm'] = $this->handleSearchForm($request);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;

        $templateData['wishlist'] = $this->get('user_manager')->getCurrentUser()->getWishlist();
        if($templateData['wishlist'])
            $this->setProductsCurrency($templateData['wishlist']->getProducts(),
                $this->get('currency_manager')->getClientCurrency());
        return $this->render('User/Wishlist.html.twig', $templateData);
    }
    

    /**
     * @Security("has_role('ROLE_USER')")
     * @Route(
     *      path="/cabinet/wishlist/update/{action}/{id}",
     *      requirements={"id" : "\d+"},
     *      options={"expose" : "true"})
     * @return JsonResponse
     */
    public function wishlistUpdateAction($id = null, $action = null )
    {
        $userManager = $this->get('user_manager');
        if($id){
            $product = $this->getDoctrine()->getManager()->getRepository("AppBundle:Product")->find($id);
            if(!$product)
                throw new NotFoundHttpException(sprintf('No product with id=$d', $id));
        }
        else
            $action = 'get';

        if($action == 'toggle')
            $wishlist = $userManager->toggleProductInWishlist($product)->getWishlist();
        elseif($action == 'add')
            $wishlist = $userManager->addProductToWishlist($product)->getWishlist();
        elseif($action == 'remove')
            $wishlist = $userManager->removeProductFromWishlist($product)->getWishlist();
        else
            $wishlist = $userManager->getCurrentUser()->getWishlist();
        if($wishlist){
            $this->setProductsCurrency($wishlist->getProducts(), $this->get('currency_manager')->getClientCurrency());
            foreach ($wishlist->getProducts() as &$product) {
                $product->wishlistThumbPath = $this->get('liip_imagine.cache.manager')
                    ->getBrowserPath($product->getMainPhoto1Path(), 'wishlist_thumb');
                $product->priceDisc = $product->getPrice(true);
            }

            $productsJson = $this->get('jms_serializer')
                ->serialize($wishlist->getProducts(), 'json', SerializationContext::create()->enableMaxDepthChecks());
            $response = new JsonResponse($productsJson);
        }
        else
            $response = new JsonResponse('null');
        return $response;
    }


}
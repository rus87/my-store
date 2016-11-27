<?php


namespace AppBundle\Controller\UserCabinet;


use AppBundle\Controller\BaseController;
use Proxies\__CG__\AppBundle\Entity\Shipping;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use JMS\Serializer\SerializationContext;
use AppBundle\Form\ShippingType;

/**
 * Class CabinetController
 * @package AppBundle\Controller\UserCabinet
 * @Security("has_role('ROLE_USER')")
 */
class CabinetController extends BaseController
{
    /**
     * @Route(path="/cabinet/wishlist")
     */
    public function showWishlistAction(Request $request)
    {
        $product = $this->getDoctrine()->getManager()->getRepository("AppBundle:Product")->find(800);
        //$this->get('user_manager')->toggleProductInWishlist($product);
        $templateData['title'] = 'Wishlist';
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

    /**
     * @param $request
     * @return Response
     * @Route(path="/cabinet/shippings")
     *
     */
    public function showShippingsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('user_manager')->getCurrentUser();
        $templateData['title'] = 'Wishlist';
        $templateData['currency'] = $this->get('currency_manager')->getClientCurrency();
        $templateData['form'] = $this->handleCurrencyForm($request, 'app_usercabinet_cabinet_showwishlist');
        if($this->currencyRedirectResponse) return $this->currencyRedirectResponse;
        $templateData['searchForm'] = $this->handleSearchForm($request);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;
        $shipping = new Shipping();
        $form = $this->createForm(ShippingType::class, $shipping,
            ['em' => $em, 'user_id' => $user->getId(), 'select_ph' => 'Create new']);
        $templateData['shippingForm'] = $form->createView();
        $form->handleRequest($request);
        if($form->isValid()){
            if($form->get('save')->isClicked()){
                if(($existingShipping = $form['shipping_select']->getData()) == null){
                    $shipping->setUser($user);
                    $em->persist($shipping);
                }
                else{
                    $existingShipping->setTitle($shipping->getTitle());
                    $existingShipping->setCompany($shipping->getCompany());
                    $existingShipping->setStorageNum($shipping->getStorageNum());
                    $existingShipping->setCity($shipping->getCity());
                    $existingShipping->setStorageAddress($shipping->getStorageAddress());
                    $existingShipping->setClientTel($shipping->getClientTel());
                    $existingShipping->setClientFio($shipping->getClientFio());
                }
            }
            if($form->get('delete')->isClicked()){
                if(($existingShipping = $form['shipping_select']->getData()) != null){
                    $em->persist($existingShipping);
                    foreach($em->getRepository('AppBundle:Booking')->findBy(['shipping' => $existingShipping]) as $booking)
                        $booking->setShipping(null);
                    $em->remove($existingShipping);
                }
            }
            $em->flush();
            return $this->redirectToRoute('app_usercabinet_cabinet_showshippings');
        }

        return $this->render('User/Shippings.html.twig', $templateData);
    }

    /**
     * @param $id
     * @Route(path="/cabinet/get-shipping/{id}",
     *      requirements={"id": "\d+"},
     *      options={"expose"=true})
     * @return JsonResponse
     */
    public function getShippingAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Shipping');
        $shipping = $repo->find($id);
        if(! $shipping)
            throw new NotFoundHttpException(sprintf("Shipping with id=%d not found", $id));
        $shippingJson = $this->get('jms_serializer')
            ->serialize($shipping, 'json');
        return new JsonResponse($shippingJson);
    }


}
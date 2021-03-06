<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\BookingType;
use AppBundle\Entity\Booking;


class CheckoutController extends BaseController
{
    /**
     * @return Response
     * @Route(path="/checkout")
     */
    public function indexAction(Request $request)
    {
        $uid = null;
        if(($user = $this->get('user_manager')->getCurrentUser()) != null)
            $uid = $user->getId();
        $em = $this->getDoctrine()->getManager();
        $currency = $this->get('currency_manager')->getClientCurrency();
        $cart = $this->get("cart_manager")->getCart();
        $this->get('currency_manager')->setProductsCurrency($cart->getProducts());
        $checkoutForm = $this->createForm(BookingType::class, new Booking(),
            [
                'attr' => ['id' => 'checkout_form', 'onSubmit' => 'send_form()'],
                'em' => $em,
                'user_id' => $uid
            ]);
        $templateData = [
            'checkoutForm' => $checkoutForm->createView(),
            'cart' => $cart,
            'categories' => $em->getRepository('AppBundle:Category')->findBy(['parent' => null]),
            'currency' => $currency
        ];
        $templateData['title'] = 'Checkout';
        $templateData['searchForm'] = $this->handleSearchForm($request);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;
        $templateData['form'] = $this->handleCurrencyForm($request, 'app_checkout_index');
        if($this->currencyRedirectResponse) return $this->currencyRedirectResponse;
        return $this->render('Checkout/checkout.html.twig', $templateData);
    }

    /**
     * @Route(path="/checkout/checkform", options={"expose" : "true"})
     */
    public function checkFormAction(Request $request)
    {
        if($request->isXmlHttpRequest())
        {
            $booking = new Booking();
            $form = $this->createForm(BookingType::class, $booking);
            $form->handleRequest($request);
            if($form->isValid()){
                $products = $this->get('cart_manager')->getCartProducts();
                if(! $products->isEmpty()){
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($booking);
                    foreach($products as $product)
                        $booking->addProduct($product);
                    $this->get('cart_manager')->clearCart();
                    $em->persist($this->get('cart_manager')->getCart());
                    $em->flush();
                }
                return new JsonResponse('OK :)');

            }
            else{
                $errors = $form->getErrors();
                $errorsJson = $this->get('jms_serializer')->serialize($errors, 'json');
                return new JsonResponse($errorsJson);
            }
        }
    }
}
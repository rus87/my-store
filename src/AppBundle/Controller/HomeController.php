<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Event\MyEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Products\Jacket;
use AppBundle\Entity\Category;
use JMS\Serializer\SerializationContext;
use AppBundle\Form\CurrencyType;
use AppBundle\Form\SearchProductsType;

class HomeController extends BaseController
{
    /**
     * @Route(path="/", options={"expose" : "true"})
     */
    public function homeAction(Request $request)
    {
        $categories = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        $form = $this->createCurrencyForm('app_home_home', []);
        $this->handleSearchForm($request);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;
        return $this->render("mybase.html.twig",
            [
                'categories' => $categories,
                'currency' => $this->get('currency_manager')->getClientCurrency(),
                'form' => $form->createView(),
                'searchForm' => $this->searchFormView
            ]);
    }

    /**
     * @Route(path = "/test")
     */
    public function testAction()
    {
        //dump($this->get("cartManager")->getProducts());
        $dispatcher = $this->get("event_dispatcher");
        $myEvent = new MyEvent("input data for event");
        $dispatcher->dispatch("my_event", $myEvent);
        //dump($this->getDoctrine()->getRepository("AppBundle:Product")->test());
        return $this->render("mybase.html.twig");
    }



}
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
use Symfony\Component\HttpFoundation\File\File;

class HomeController extends BaseController
{
    /**
     * @Route(path="/", options={"expose" : "true"})
     */
    public function homeAction(Request $request)
    {
        $templateData['categories'] = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        $templateData['currency'] = $this->get('currency_manager')->getClientCurrency();
        $templateData['title'] = 'Home';
        $templateData['form'] = $this->handleCurrencyForm($request, 'app_home_home', []);
        if($this->currencyRedirectResponse) return $this->currencyRedirectResponse;
        $templateData['searchForm'] = $this->handleSearchForm($request);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;
        return $this->render("mybase.html.twig", $templateData);
    }

    /**
     * @Route(path = "/test")
     */
    public function testAction()
    {
        $dispatcher = $this->get("event_dispatcher");
        $myEvent = new MyEvent("input data for event");
        $dispatcher->dispatch("my_event", $myEvent);

        return $this->render("mybase.html.twig");
    }

    private function setRandomDiscounts()
    {
        $em = $this->getDoctrine()->getManager();
        $products = $em->getRepository('AppBundle:Product')->findAll();
        $discChance = [false, false, true];
        $discVals = [10, 20, 30];
        foreach ($products as $product) {
            $discount = $discChance[array_rand($discChance)];
            if($discount){
                $discVal = $discVals[array_rand($discVals)];
                $product->setDiscount($discVal);
            }
        }
        $em->flush();

    }

}
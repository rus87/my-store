<?php
/**
 * Created by PhpStorm.
 * User: volkhonovich.ri
 * Date: 21.10.2016
 * Time: 9:20
 */

namespace AppBundle\Utils\Filters;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\FormFactory;
use AppBundle\Form\Filters\JacketType;
use AppBundle\Form\Filters\SweaterType;
use AppBundle\Form\Filters\BlouseType;
use AppBundle\Form\Filters\TrousersType;
use AppBundle\Form\FiltersType;


class FiltersHandler
{
    private $requestStack;
    private $em;
    private $router;
    private $formFactory;

    const FILTERS_CLASS_PATTERN = "AppBundle\\Utils\\Filters\\FilterClasses\\%sFilter";

    /**
     * FiltersHandler constructor.
     * @param RequestStack $requestStack
     * @param EntityManager $em
     * @param Router $router
     * @param FormFactory $formFactory
     */
    public function __construct(RequestStack $requestStack, EntityManager $em, Router $router, FormFactory $formFactory)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
        $this->router = $router;
        $this->formFactory = $formFactory;
    }

    public function handleForm($redirectRoute, $params, $categoryName = null)
    {
        $request = $this->requestStack->getCurrentRequest();
        $formData = [];
        $className = $this->em->getRepository("AppBundle:Category")->getProductsClassName($categoryName);
        $className === null ? $fullProductClassName = 'AppBundle\Entity\Product' :
            $fullProductClassName = 'AppBundle\Entity\Products\\'.ucfirst($className);
        foreach($fullProductClassName::getAvailableFilters() as $filterName){
            $filterClass = $this->getFullFilterClass($filterName);
            $filter = new $filterClass($request->query->get($filterName));
            $formData[$filter->getId()] = $filter->getRawValue();
            //dump($filter);
            //dump($request->query->get($filterName));
        }
        switch ($className){
            case 'Jacket': {
                $form = $this->formFactory->create(JacketType::class, $formData);
                break;
            }

            case 'Sweater': {
                $form = $this->formFactory->create(SweaterType::class, $formData);
                break;
            }

            case 'Blouse': {
                $form = $this->formFactory->create(BlouseType::class, $formData);
                break;
            }

            case 'Trousers': {
                $form = $this->formFactory->create(TrousersType::class, $formData);
                break;
            }

            default:{
                $form = $this->formFactory->create(FiltersType::class, $formData);
            }
        }
        $form->handleRequest($request);
        if($form->isValid() && $form->isSubmitted()){
            $params = array_merge($params, $form->getData());
            return new RedirectResponse($this->router->generate($redirectRoute, $params), 302);
        }
        return $form->createView();
    }

    public function getFilters($fullProductClassName)
    {
        $filters = [];
        $request = $this->requestStack->getCurrentRequest();
        $params = $request->query->all();
        foreach($params as $key => $value){
            if(in_array($key, $fullProductClassName::getAvailableFilters())){
                $filterClass = self::getFullFilterClass($key);
                $filters[] = new $filterClass($value);
            }
        }
        return $filters;
    }

    public static function getFullFilterClass($className)
    {
        return sprintf(self::FILTERS_CLASS_PATTERN, ucfirst($className));
    }
}
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
use AppBundle\Form\Filters\ByBrandType;
use AppBundle\Form\Filters\SearchType;
use AppBundle\Form\FiltersType;
use AppBundle\Utils\CurrencyManager;


class FiltersHandler
{
    private $requestStack;
    private $em;
    private $router;
    private $formFactory;
    private $cm;

    const FILTERS_CLASS_PATTERN = "AppBundle\\Utils\\Filters\\FilterClasses\\%sFilter";
    const FILTER_SETS = [
        'by_brand' => ['priceMin', 'priceMax', 'gender'],
        'search' => ['priceMin', 'priceMax', 'gender', 'brand'],
    ];

    /**
     * FiltersHandler constructor.
     * @param RequestStack $requestStack
     * @param EntityManager $em
     * @param Router $router
     * @param FormFactory $formFactory
     */
    public function __construct(RequestStack $requestStack, EntityManager $em, Router $router, FormFactory $formFactory, CurrencyManager $cm)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->cm = $cm;
    }

    public function handleForm($redirectRoute, $params, $type = null)
    {
        $catRepo = $this->em->getRepository("AppBundle:Category");
        $request = $this->requestStack->getCurrentRequest();
        $formData = [];
        if($type == 'search')
            foreach($this->createFiltersFromQuery(self::FILTER_SETS['search']) as $filter)
                $formData[$filter->getId()] = $filter->getRawValue();
        elseif($type == 'by_brand')
            foreach($this->createFiltersFromQuery(self::FILTER_SETS['by_brand']) as $filter)
                $formData[$filter->getId()] = $filter->getRawValue();
        else{
            $className = $catRepo->getProductsClassName($type, true);
            $type = $catRepo->getProductsClassName($type);
            foreach($this->createFiltersFromQuery($className::getAvailableFilters()) as $filter)
                $formData[$filter->getId()] = $filter->getRawValue();
        }
        $queryBrands = $request->query->get('brand');
        if($queryBrands == null)
            $checked = 'all';
        else
            $checked = explode(".", $queryBrands);
        switch ($type){
            case 'Jacket': {
                $form = $this->formFactory->create(JacketType::class, $formData,
                    ['cat' => $type, 'em' => $this->em, 'checked' => $checked]);
                break;
            }

            case 'Sweater': {
                $form = $this->formFactory->create(SweaterType::class, $formData,
                    ['cat' => $type, 'em' => $this->em, 'checked' => $checked]);
                break;
            }

            case 'Blouse': {
                $form = $this->formFactory->create(BlouseType::class, $formData,
                    ['cat' => $type, 'em' => $this->em, 'checked' => $checked]);
                break;
            }

            case 'Trousers': {
                $form = $this->formFactory->create(TrousersType::class, $formData,
                    ['cat' => $type, 'em' => $this->em, 'checked' => $checked]);
                break;
            }

            case 'by_brand':{
                $form = $this->formFactory->create(ByBrandType::class, $formData,
                    ['em' => $this->em, 'checked' => $checked]);
                break;
            }

            case 'search':{
                $form = $this->formFactory->create(SearchType::class, $formData,
                    ['em' => $this->em, 'checked' => $checked]);
                break;
            }

            default:{
                $form = $this->formFactory->create(FiltersType::class, $formData,
                    ['cat' => $type, 'em' => $this->em, 'checked' => $checked]);
            }
        }
        $form->handleRequest($request);
        if($form->isValid() && $form->isSubmitted()){
            $params = array_merge($params, $form->getData());
            $brandsStr = '';
            if(isset($params['brand'])){
                $brandsCount = count($params['brand']);
                $i=0;
                foreach($params['brand'] as $brand){
                    $brandsStr .= $brand->getId();
                    if($i < $brandsCount-1)
                        $brandsStr .= '.';
                    $i++;
                }
                $params['brand'] = $brandsStr;
            }
            foreach($params as $key => $value)
                if(! $value)
                    unset($params[$key]);
            return new RedirectResponse($this->router->generate($redirectRoute, $params), 302);
        }
        return $form->createView();
    }

    public function getFiltersByClass($fullProductClassName = null)
    {
        $filters = [];
        $request = $this->requestStack->getCurrentRequest();
        $params = $request->query->all();
        foreach($params as $key => $value)
            if(in_array($key, $fullProductClassName::getAvailableFilters())){
                $filterClass = self::getFullFilterClass($key);
                if($key == 'priceMin' || $key == 'priceMax'){
                    $filters[] = new $filterClass($value, $this->cm);
                    continue;
                }
                $filters[] = new $filterClass($value);
            }

        return $filters;
    }


    public static function getFullFilterClass($className)
    {
        return sprintf(self::FILTERS_CLASS_PATTERN, ucfirst($className));
    }

    public function createFiltersFromQuery($allowedFilters)
    {
        $filters = [];
        $request = $this->requestStack->getCurrentRequest();
        $queryParams = $request->query->all();
        foreach($queryParams as $key => $value)
            if(in_array($key, $allowedFilters)){
                $class = self::getFullFilterClass($key);
                if($key == 'priceMin' || $key == 'priceMax'){
                    $filters[] = new $class($value, $this->cm);
                    continue;
                }
                $filters[] = new $class($value);
            }
        return $filters;

    }
}
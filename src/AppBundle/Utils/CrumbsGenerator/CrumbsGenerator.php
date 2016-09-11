<?php

namespace AppBundle\Utils\CrumbsGenerator;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use AppBundle\Utils\CrumbsGenerator\InputData;
use AppBundle\Utils\CrumbsGenerator\Crumb;

class CrumbsGenerator
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;

    }

    /**
     * @param InputData[] $input
     * @return array
     */
    public function make($input)
    {
        $crumbs = [];
        foreach($input as $item)
        {
            $crumb = new Crumb;
            switch ($item->getRouteName()){
                case 'app_home_home':
                    $crumb->setLink($this->router->generate('app_home_home'));
                    $crumb->setMark('Home');
                    $crumbs []= $crumb;
                    break;

                case 'app_products_productsbygender':
                    $item->getRouteParams()['gender'] == 'male' ? $crumb->setMark('Men') : $crumb->setMark('Women');
                    $crumb->setLink($this->router->generate($item->getRouteName(), $item->getRouteParams()));
                    $crumbs []= $crumb;
                    break;

                case 'app_products_productsshow':
                    $crumb->setLink($this->router->generate($item->getRouteName(), $item->getRouteParams()));
                    $crumb->setMark(ucfirst($item->getRouteParams()['category']));
                    if($crumb->getMark(){mb_strlen($crumb->getMark()) - 1} != 's')
                        $crumb->setMark($crumb->getMark().'s');
                    $crumbs []= $crumb;
                    break;
            }
        }
        $crumbs[count($crumbs) - 1]->setIsLast(true);
        return $crumbs;
    }
}
<?php

namespace AppBundle\Utils\CrumbsGenerator;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use AppBundle\Utils\CrumbsGenerator\InputData;
use AppBundle\Utils\CrumbsGenerator\Crumb;
use Doctrine\ORM\EntityManager;

class CrumbsGenerator
{
    private $router;
    private $em;

    public function __construct(Router $router, EntityManager $em)
    {
        $this->router = $router;
        $this->em = $em;
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

                case 'app_products_showbycategory':
                    $crumb->setLink($this->router->generate($item->getRouteName(), $item->getRouteParams()));
                    $crumb->setMark(ucfirst($item->getRouteParams()['categoryName']));
                    if($crumb->getMark()[mb_strlen($crumb->getMark()) - 1] != 's')
                        $crumb->setMark($crumb->getMark().'s');
                    $crumbs []= $crumb;
                    break;

                case 'app_product_show':
                    $crumb->setMark(ucfirst($item->getMark()));
                    $crumbs []= $crumb;
                    break;

                case 'app_products_showsearchresults':
                    $crumb->setMark(ucfirst($item->getMark()));
                    $crumbs []= $crumb;
                    break;

                case 'app_products_showbybrand':
                    $crumb->setMark($this->em->getRepository('AppBundle:Brand')->find($item->getRouteParams()['brandId'])->getTitle());
                    $crumb->setLink($this->router->generate('app_products_showbybrand', $item->getRouteParams()));
                    $crumbs []= $crumb;
                    break;

            }
        }
        $crumbs[count($crumbs) - 1]->setIsLast(true);
        return $crumbs;
    }

}
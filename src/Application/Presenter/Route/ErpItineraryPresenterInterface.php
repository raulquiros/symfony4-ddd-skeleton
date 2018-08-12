<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 12/6/18
 * Time: 10:25
 */

namespace App\Application\Presenter\Route;

use App\Domain\Entity\Route;

interface ErpItineraryPresenterInterface
{
    /**
     * @param Route $route
     * @param array $routesArr
     * @return mixed
     */
    public function write(Route $route, array $routesArr);
}
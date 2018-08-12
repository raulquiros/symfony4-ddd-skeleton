<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 12/6/18
 * Time: 10:25
 */

namespace App\Application\Presenter\Route;

use \App\Domain\Entity\Route;
use App\Domain\Entity\Supplier;

interface ErpRoutePresenterInterface
{
    public function write(Route $route, Supplier $supplier);
}
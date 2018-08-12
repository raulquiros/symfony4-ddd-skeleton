<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 12/6/18
 * Time: 10:25
 */

namespace App\Application\Presenter\Route;

use App\Domain\Entity\Client;
use \App\Domain\Entity\Route;

class ArrayErpItineraryPresenter implements ErpItineraryPresenterInterface
{

    /**
     * @param Route $route
     * @param array $routesArr
     * @return array
     */
    public function write(Route $route, array $routesArr) :array
    {
        $itinerary = [];

        $itinerary["reference"] = explode("/", $route->route()->reference())[0];
        $itinerary["branch_id"] = $route->route()->branchId();
        $itinerary["platform_id"] = $route->route()->platformId();
        $itinerary["comunicate_to"] = $route->route()->communicateTo();
        $itinerary["invoice_to"] = $route->route()->invoiceTo();
        $itinerary["invoice_type"] = $route->route()->invoiceType();
        $itinerary["analyticaccounting"] = null;
        $itinerary["client"] = $this->serializeClient($route->client());
        $itinerary["agent"] = $this->serializeClient($route->agent());
        $itinerary["routes"] = $routesArr;
        $itinerary["date"] = $route->route()->serviceLocalDateStart()->format("Y-m-d H:i");

        return ["itinerary" => $itinerary];
    }


    private function serializeClient(Client $entityClient) : array
    {

        $client["client_code"] = $entityClient->client()->clientCode();
        $client["branch_id"] = $entityClient->client()->branchId();
        $client["currency"] = $entityClient->client()->currencyCode();
        $client["language"] = $entityClient->client()->language();
        $client["name"] = $entityClient->client()->name();
        $client["tax"] = $entityClient->client()->tax();
        $client["address"] = [
            "address_id" => $entityClient->invoiceAddress()->addressId(),
            "city" => $entityClient->invoiceAddress()->city(),
            "region" => "",
            "address" => $entityClient->invoiceAddress()->address(),
            "address1" => $entityClient->invoiceAddress()->address1(),
            "postcode" => $entityClient->invoiceAddress()->postcode(),
            "country" => $entityClient->invoiceAddress()->countryCode(),
            "OB_id" => $entityClient->invoiceAddress()->OBId(),
            "registration" => $entityClient->invoiceAddress()->registration(),
            "name" => $entityClient->invoiceAddress()->name()
        ];
        $client["email"] = $entityClient->client()->email();
        $client["email_admin"] = $entityClient->client()->email();
        $client["phone"] = $entityClient->client()->phone();
        $client["commission"] = $entityClient->client()->commission();
        $client["phone_extra"] = $entityClient->client()->phoneExtra();
        $client["country"] = $entityClient->client()->countryCode();
        $client["bpartner_type"] = $entityClient->client()->bpartnerTypeId();
        $client["paymentmethod"] = $entityClient->client()->paymentMethodId();
        $client["paymentterms"] = $entityClient->client()->paymentTermsId();
        $client["business_type"] = $entityClient->client()->businessTypeId();
        $client["vat_type_id"] = $entityClient->client()->vatTypeId();
        $client["fiscal"] = $entityClient->client()->fiscal();
        $client["credit_limit"] = $entityClient->client()->creditLimit();
        $client["is_company"] = $entityClient->client()->isCompany();

        return $client;
    }

}
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
use App\Domain\Entity\Segment\Segment;
use App\Domain\Entity\Supplier;

class ArrayErpRoutePresenter implements ErpRoutePresenterInterface
{

    /**
     * @param Route $entityRoute
     * @param Supplier $entitySupplier
     * @return array
     */
    public function write(Route $entityRoute, Supplier $entitySupplier) :array
    {
        $route = [];

        $route["reference"] = $entityRoute->route()->reference();
        $route["agent_reference"] = $entityRoute->route()->clientReference();
        $route["pax"] = $entityRoute->route()->passengers();
        $route["route_type"] = $entityRoute->route()->typeId();
        $route["supplier_reference"] = $entityRoute->route()->supplierReference();
        $route["vehicle_class"] = $entityRoute->route()->vehicleCategory()->vehicleClass()->name();
        $route["vehicle_los"] = $entityRoute->route()->vehicleCategory()->levelOfService()->name();
        $route["description"] = "";
        $route["date"] = $entityRoute->route()->serviceLocalDateStart()->format("Y-m-d H:i");
        $route["passenger"] = $entityRoute->route()->passengerName();
        $route["staff"] = "";
        $route["staffEmail"] = "";

        $index = 1;
        foreach($entityRoute->segments() as $entitySegment) {
            $route["segment"][] = $this->serializeSegment($entitySegment, $index);
            $index++;
        }

        $sii = [];
        $sii["operation_date"] = $entityRoute->route()->serviceLocalDateStart()->format("Y-m-d H:i");
        $sii["description"] = $entityRoute->route()->typeId() == Route::TYPE_TRANSFE ? "Traslado" : "Servicio a disposición";
        $sii["invoice_key"] = "Factura";
        $route["sii"] = $sii;

        $route["purchase"][] = $this->serializePurchase($entityRoute->routeCost(), $sii);
        $route["sale"] = $this->serializeSale($entityRoute->routeCost()->sale());

        $commissions = [];
        if(!empty($entityRoute->commission())) {
            $commissions["agent"] = $this->serializeClient($entityRoute->commission()->agent());
            $commissions["ammount"] = $entityRoute->commission()->ammount()->value();
            $commissions["base_comission"] = $entityRoute->commission()->baseCommission()->value();
        }
        $route["commissions"][] = $commissions;

        $supplier = $this->serializeSupplier($entitySupplier);
        $route["supplier"] = $supplier;

        $payments = [];
        $route["payments"] = $payments;

        return $route;
    }

    /**
     * @param Segment $entitySegment
     * @return array
     */
    private function serializeSegment($entitySegment, $index): array
    {
        $segment = [];
        $segment["index"] = $index;
        $segment["stop_time"] = $entitySegment->datetime()->format("Y-m-d H:i");
        $segment["location_type"] = $entitySegment->transportType();
        $segment["extra_info"] = $entitySegment->flightReference();
        $segment["airport_code"] = $entitySegment->location()->iata();
        $segment["latitude"] = $entitySegment->location()->latitude();
        $segment["longitude"] = $entitySegment->location()->longitude();
        $segment["description"] = $entitySegment->location()->name();
        return $segment;
    }

    /**
     * @param Route\RoutePrice $routeCost
     * @param array $sii
     * @return array
     */
    private function serializePurchase(Route\RoutePrice $routeCost, array $sii): array
    {
        $arrayPurchase = $routeCost->purchase();
        $purchase = [];

        foreach ($arrayPurchase as $entityPurchase) {
            $charges = [];
            $charges["product_id"] = $entityPurchase->productId();
            $charges["description"] = $entityPurchase->description();
            $charges["quantity"] = $entityPurchase->quantity();
            $charges["unitary_price"] = $entityPurchase->totalAmount()->value();
            $charges["discount"] = $entityPurchase->price()->discount()->value();
            $charges["type"] = $entityPurchase->expense() ? Route\RoutePurchase::TYPE_EXPENSE : Route\RoutePurchase::TYPE_CHARGE;
            $charges["base_price"] = $entityPurchase->price()->base()->value();
            $charges["tax_amount"] = $entityPurchase->price()->tax()->value();
            $charges["tax_rate"] = $entityPurchase->taxCostPercent();

            $generateInvoice = $entityPurchase->invoiceStatus();

            $purchase["charges"][] = $charges;
        }

        $purchase["rate_exchange_currency"] = $routeCost->exchange()->exchange();
        $purchase["currency_exchange_date"] = $routeCost->exchange()->exchangeDate()->format("Y-m-d H:i");
        $purchase["currency"] = $routeCost->exchange()->purchaseCurrency();

        $purchase["generate_invoice"] = $generateInvoice;
        $purchase["branch_mng"] = "";
        $purchase["autoinvoice"] = 0;

        $purchase["sii"] = $sii;

        return $purchase;
    }

    /**
     * @param Route\RouteSale[] $arraySale
     * @return array
     */
    private function serializeSale(array $arraySale): array
    {
        $sale = [];
        $sale["currency"] = "";

        foreach ($arraySale as $entitySale) {
            $charge = [];
            $charge["product_id"] = $entitySale->productId();
            $charge["description"] = $entitySale->description();
            $charge["quantity"] = $entitySale->quantity();
            $charge["unitary_price"] = $entitySale->totalAmount()->value();
            $charge["discount"] = $entitySale->price()->discount()->value();
            $charge["type"] = $entitySale->expense() ? Route\RouteSale::TYPE_EXPENSE : Route\RouteSale::TYPE_CHARGE;
            $charge["base_price"] = $entitySale->price()->base()->value();
            $charge["tax_amount"] = $entitySale->price()->tax()->value();
            $charge["tax_rate"] = $entitySale->taxCostPercent();

            $generateInvoice = $entitySale->invoiceStatus();

            $sale["charges"][] = $charge;
        }

        $sale["generate_invoice"] = $generateInvoice;

        return $sale;
    }

    /**
     * @param Supplier $entitySupplier
     * @return array
     */
    private function serializeSupplier(Supplier $entitySupplier): array
    {
        $supplier = [];
        $supplier["supplier_code"] = $entitySupplier->supplier()->supplierCode();
        $supplier["currency"] = $entitySupplier->supplier()->currencyCode();
        $supplier["language"] = $entitySupplier->supplier()->language();
        $supplier["name"] = $entitySupplier->supplier()->name();
        $supplier["tax"] = $entitySupplier->supplier()->vatId();
        $supplier["email"] = $entitySupplier->supplier()->email();
        $supplier["fiscal"] = $entitySupplier->supplier()->fiscal();
        $supplier["phone"] = $entitySupplier->supplier()->phoneNumber();
        $supplier["branch_id"] = $entitySupplier->supplier()->branchId();

        $address = [];
        $address["address_id"] = $entitySupplier->invoiceAddress()->addressId();
        $address["city"] = $entitySupplier->invoiceAddress()->city();
        $address["address"] = $entitySupplier->invoiceAddress()->address();
        $address["address1"] = $entitySupplier->invoiceAddress()->address1();
        $address["postcode"] = $entitySupplier->invoiceAddress()->postcode();
        $address["country"] = $entitySupplier->invoiceAddress()->countryCode();
        $address["county"] = $entitySupplier->invoiceAddress()->county();
        $address["OB_id"] = $entitySupplier->invoiceAddress()->OBId();
        $address["registration"] = $entitySupplier->invoiceAddress()->registration();
        $address["name"] = $entitySupplier->invoiceAddress()->name();
        $supplier["address"] = $address;

        $supplier["headoffice"] = !empty($entitySupplier->supplier()->headoffice()) ? $this->serializeSupplier($entitySupplier->supplier()->headoffice()): null;
        $supplier["country"] = $entitySupplier->supplier()->countryCode();
        $supplier["type"] = $entitySupplier->supplier()->typeId();
        $supplier["contact_name"] = $entitySupplier->supplier()->contactName();
        $supplier["paymentmethod"] = $entitySupplier->supplier()->paymentMethodId();
        $supplier["paymentterms"] = $entitySupplier->supplier()->paymentTermsId();
        $supplier["vat_type_id"] = $entitySupplier->supplier()->vatTypeId();
        $supplier["autoinvoice"] = $entitySupplier->supplier()->autoInvoice() == true ? 1 : 0;
        $supplier["foreride_operative"] = $entitySupplier->supplier()->forerideOperative() == true ? 1 : 0;

        return $supplier;
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
        ];
        $client["email"] = $entityClient->client()->email();
        $client["phone"] = $entityClient->client()->phone();
        $client["commission"] = $entityClient->client()->commission();
        $client["phone_extra"] = $entityClient->client()->phoneExtra();
        $client["country"] = $entityClient->client()->countryCode();
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
<?php

namespace Booni3\DhlExpressRest\API;

use Booni3\DhlExpressRest\DHL;
use Booni3\DhlExpressRest\Response\RatesResponse;
use Booni3\DhlExpressRest\DTO\ShipmentCreator;

class Rates extends Client
{
    public function retrieveSingle(ShipmentCreator $creator): RatesResponse
    {
        $account = current($creator->accounts());
        $accountNumber = $account ? $account['number'] : '';

        $packages = $creator->packageWeightAndDimensionsOnly();
        $package = current($packages);

        return RatesResponse::fromArray(
            $this->get('rates', [
                'accountNumber' => $accountNumber,
                'originCountryCode' => $creator->shipper->getCountryCode(),
                'originCityName' => $creator->shipper->getCityName(),
                'destinationCountryCode' => $creator->receiver->getCountryCode(),
                'destinationCityName' => $creator->receiver->getCityName(),
                'weight' => $package['weight'],
                'length' => $package['dimensions']['length'],
                'width' => $package['dimensions']['width'],
                'height' => $package['dimensions']['height'],
                'plannedShippingDate' => $creator->readyAt->format(DHL::DATE_FORMAT),
                'isCustomsDeclarable' => var_export($creator->customsDeclarable, true),
                'unitOfMeasurement' => 'metric',
                'nextBusinessDay' => var_export(true, true),
                'requestEstimatedDeliveryDate' => var_export(true, true),
            ])
        );
    }

    public function retrieve(ShipmentCreator $creator): RatesResponse
    {
        return RatesResponse::fromArray(
            $this->post('rates', [
                "customerDetails" => [
                    "shipperDetails" => $creator->shipper->toArray()['postalAddress'],
                    "receiverDetails" => $creator->receiver->toArray()['postalAddress']
                ],
                'accounts' => $creator->accounts(),
                //"productCode" => null,
                "plannedShippingDateAndTime" => $creator->readyAt->format(DHL::TIME_FORMAT),
                "unitOfMeasurement" => "metric",
                "isCustomsDeclarable" => $creator->customsDeclarable,
                "monetaryAmount" => [
                    [
                        "typeCode" => "declaredValue",
                        "value" => 100,
                        "currency" => "GBP"
                    ]
                ],
                "packages" => $creator->packageWeightAndDimensionsOnly()
            ])
        );
    }
}

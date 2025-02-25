<?php

namespace Booni3\DhlExpressRest\API;

use Carbon\Carbon;

class Tracking extends Client
{
    public function single(string $trackingNumber)
    {
        return $this->get("shipments/$trackingNumber/tracking", [
            'trackingView' => 'all-checkpoints',
            'levelOfDetail' => 'all'
        ]);
    }

    public function multi(array $trackingNumbers, ?Carbon $from = null, ?Carbon $to = null)
    {
        $tracking = array_map(function ($tracking) {
            return ['shipmentTrackingNumber' => $tracking];
        }, $trackingNumbers);

        $data = array_filter([
            'dateRangeFrom' => $from ? $from->format('Y-m-d') : null,
            'dateRangeTo' => $to ? $to->format('Y-m-d') : null,
            'trackingView' => 'all-checkpoints',
            'levelOfDetail' => 'all'
        ], function ($row) {
            return $row;
        });

        return $this->get("tracking", $tracking + $data);
    }
}

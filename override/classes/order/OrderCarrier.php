<?php

class OrderCarrier extends OrderCarrierCore
{

    /** @var string */
    public $return_tracking_number;

    /** @var string */
    public $return_carrier;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'order_carrier',
        'primary' => 'id_order_carrier',
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_carrier' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_order_invoice' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'weight' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'shipping_cost_tax_excl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'shipping_cost_tax_incl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'tracking_number' => ['type' => self::TYPE_STRING, 'validate' => 'isTrackingNumber'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'return_tracking_number' => ['type' => self::TYPE_STRING, 'validate' => 'isTrackingNumber'],
            'return_carrier' => ['type' => self::TYPE_STRING, 'validate' => '']
        ],
    ];
}

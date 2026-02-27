<?php

/**
 * Copyright 2017 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2017 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
/*
 * Lengow Order Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowOrder extends Order
{
    /**
     * @var string Lengow order table name
     */
    public const TABLE_ORDER = 'lengow_orders';
    public const TABLE_ORDER_LINE = 'lengow_order_line';

    /* Order fields */
    public const FIELD_ID = 'id';
    public const FIELD_ORDER_ID = 'id_order';
    public const FIELD_SHOP_ID = 'id_shop';
    public const FIELD_SHOP_GROUP_ID = 'id_shop_group';
    public const FIELD_LANG_ID = 'id_lang';
    public const FIELD_FLUX_ID = 'id_flux';
    public const FIELD_DELIVERY_ADDRESS_ID = 'delivery_address_id';
    public const FIELD_DELIVERY_COUNTRY_ISO = 'delivery_country_iso';
    public const FIELD_MARKETPLACE_SKU = 'marketplace_sku';
    public const FIELD_MARKETPLACE_NAME = 'marketplace_name';
    public const FIELD_MARKETPLACE_LABEL = 'marketplace_label';
    public const FIELD_ORDER_LENGOW_STATE = 'order_lengow_state';
    public const FIELD_ORDER_PROCESS_STATE = 'order_process_state';
    public const FIELD_ORDER_DATE = 'order_date';
    public const FIELD_ORDER_ITEM = 'order_item';
    public const FIELD_ORDER_TYPES = 'order_types';
    public const FIELD_CURRENCY = 'currency';
    public const FIELD_TOTAL_PAID = 'total_paid';
    public const FIELD_COMMISSION = 'commission';
    public const FIELD_CUSTOMER_NAME = 'customer_name';
    public const FIELD_CUSTOMER_EMAIL = 'customer_email';
    public const FIELD_CUSTOMER_VAT_NUMBER = 'customer_vat_number';
    public const FIELD_CARRIER = 'carrier';
    public const FIELD_CARRIER_METHOD = 'method';
    public const FIELD_CARRIER_TRACKING = 'tracking';
    public const FIELD_REFUND_REASON = 'refund_reason';
    public const FIELD_REFUND_MODE = 'refund_mode';
    public const FIELD_REFUNDED = 'refunded';
    public const FIELD_QUANTITY_REFUNDED = 'quantity_refunded';
    public const FIELD_CARRIER_RELAY_ID = 'id_relay';
    public const FIELD_SENT_MARKETPLACE = 'sent_marketplace';
    public const FIELD_IS_REIMPORTED = 'is_reimported';
    public const FIELD_MESSAGE = 'message';
    public const FIELD_CREATED_AT = 'date_add';
    public const FIELD_EXTRA = 'extra';

    /* Order process states */
    public const PROCESS_STATE_NEW = 0;
    public const PROCESS_STATE_IMPORT = 1;
    public const PROCESS_STATE_FINISH = 2;

    /* Order states */
    public const STATE_ACCEPTED = 'accepted';
    public const STATE_WAITING_SHIPMENT = 'waiting_shipment';
    public const STATE_SHIPPED = 'shipped';
    public const STATE_CLOSED = 'closed';
    public const STATE_REFUSED = 'refused';
    public const STATE_CANCELED = 'canceled';
    public const STATE_REFUNDED = 'refunded';
    public const STATE_PARTIALLY_REFUNDED = 'partial_refunded';

    /* Order types */
    public const TYPE_PRIME = 'is_prime';
    public const TYPE_EXPRESS = 'is_express';
    public const TYPE_BUSINESS = 'is_business';
    public const TYPE_DELIVERED_BY_MARKETPLACE = 'is_delivered_by_marketplace';
    public const FIELD_B2B_VALUE = 'B2B';

    /**
     * @var number of tries to sync order num
     */
    public const SYNCHRONIZE_TRIES = 5;

    /**
     * @var string label fulfillment for old orders without order type
     */
    public const LABEL_FULFILLMENT = 'Fulfillment';

    /**
     * @var string Lengow order record id
     */
    public string $lengowId = '';

    /**
     * @var int PrestaShop shop ID
     */
    public int $lengowIdShop = 0;

    /**
     * @var int|null Lengow flux id
     */
    public ?int $lengowIdFlux = null;

    /**
     * @var int id of the delivery address
     */
    public int $lengowDeliveryAddressId = 0;

    /**
     * @var string ISO code for country
     */
    public string $lengowDeliveryCountryIso = '';

    /**
     * @var string Lengow order id
     */
    public string $lengowMarketplaceSku = '';

    /**
     * @var string marketplace's code
     */
    public string $lengowMarketplaceName = '';

    /**
     * @var string marketplace's label
     */
    public string $lengowMarketplaceLabel = '';

    /**
     * @var string current Lengow state
     */
    public string $lengowState = '';

    /**
     * @var int Lengow process state (0 => error, 1 => imported, 2 => finished)
     */
    public int $lengowProcessState = 0;

    /**
     * @var string marketplace order date
     */
    public string $lengowOrderDate = '';

    /**
     * @var int number of items
     */
    public int $lengowOrderItem = 0;

    /**
     * @var array<string, mixed> order types (is_express, is_prime...)
     */
    public array $lengowOrderTypes = [];

    /**
     * @var string order currency
     */
    public string $lengowCurrency = '';

    /**
     * @var float total paid on marketplace
     */
    public float $lengowTotalPaid = 0.0;

    /**
     * @var string Customer vat number
     */
    public string $lengowCustomerVatNumber = '';

    /**
     * @var float commission on marketplace
     */
    public float $lengowCommission = 0.0;

    /**
     * @var string the name of the customer
     */
    public string $lengowCustomerName = '';

    /**
     * @var string email of the customer
     */
    public string $lengowCustomerEmail = '';

    /**
     * @var string carrier from marketplace
     */
    public string $lengowCarrier = '';

    /**
     * @var string carrier Method from marketplace
     */
    public string $lengowMethod = '';

    /**
     * @var string tracking
     */
    public string $lengowTracking = '';

    /**
     * @var string id relay
     */
    public string $lengowIdRelay = '';

    /**
     * @var bool order shipped by marketplace
     */
    public bool $lengowSentMarketplace = false;

    /**
     * @var bool order is reimported (ready to be reimported)
     */
    public bool $lengowIsReimported = false;

    /**
     * @var string message
     */
    public string $lengowMessage = '';

    /**
     * @var string creation order date
     */
    public string $lengowDateAdd = '';

    /**
     * @var string extra information (json node form import)
     */
    public string $lengowExtra = '';

    /**
     * Construct a Lengow order based on PrestaShop order
     *
     * @param int|null $id Lengow order id
     * @param int|null $idLang PrestaShop id lang
     */
    public function __construct(?int $id = null,?int $idLang = null)
    {
        parent::__construct($id, $idLang);
        $this->loadLengowFields();
    }

    /**
     * Load information from lengow_orders table
     *
     * @return bool
     */
    protected function loadLengowFields(): bool
    {
        $query = 'SELECT
            lo.`id`,
            lo.`id_shop`,
            lo.`id_flux`,
            lo.`delivery_address_id`,
            lo.`delivery_country_iso`,
            lo.`marketplace_sku`,
            lo.`marketplace_name`,
            lo.`marketplace_label`,
            lo.`order_lengow_state`,
            lo.`order_process_state`,
            lo.`order_date`,
            lo.`order_item`,
            lo.`order_types`,
            lo.`currency`,
            lo.`total_paid`,
            lo.`customer_vat_number`,
            lo.`commission`,
            lo.`customer_name`,
            lo.`customer_email`,
            lo.`carrier`,
            lo.`method`,
            lo.`tracking`,
            lo.`id_relay`,
            lo.`sent_marketplace`,
            lo.`is_reimported`,
            lo.`message`,
            lo.`date_add`,
            lo.`extra`
            FROM `' . _DB_PREFIX_ . 'lengow_orders` lo
            WHERE lo.id_order = \'' . (int) $this->id . '\'
        ';
        if ($result = Db::getInstance()->getRow($query)) {
            $this->lengowId = $result[self::FIELD_ID];
            $this->lengowIdShop = (int) $result[self::FIELD_SHOP_ID];
            $this->lengowIdFlux = $result[self::FIELD_FLUX_ID];
            $this->lengowDeliveryAddressId = (int) $result[self::FIELD_DELIVERY_ADDRESS_ID];
            $this->lengowDeliveryCountryIso = $result[self::FIELD_DELIVERY_COUNTRY_ISO];
            $this->lengowMarketplaceSku = $result[self::FIELD_MARKETPLACE_SKU];
            $this->lengowMarketplaceName = $result[self::FIELD_MARKETPLACE_NAME];
            $this->lengowMarketplaceLabel = $result[self::FIELD_MARKETPLACE_LABEL];
            $this->lengowState = $result[self::FIELD_ORDER_LENGOW_STATE];
            $this->lengowProcessState = (int) $result[self::FIELD_ORDER_PROCESS_STATE];
            $this->lengowOrderDate = $result[self::FIELD_ORDER_DATE];
            $this->lengowOrderItem = (int) $result[self::FIELD_ORDER_ITEM];
            $this->lengowOrderTypes = $result[self::FIELD_ORDER_TYPES] !== null
                ? json_decode($result[self::FIELD_ORDER_TYPES], true)
                : [];
            $this->lengowCurrency = $result[self::FIELD_CURRENCY];
            $this->lengowTotalPaid = $result[self::FIELD_TOTAL_PAID];
            $this->lengowCustomerVatNumber = $result[self::FIELD_CUSTOMER_VAT_NUMBER] !== null
                ? $result[self::FIELD_CUSTOMER_VAT_NUMBER]
                : '';
            $this->lengowCommission = $result[self::FIELD_COMMISSION];
            $this->lengowCustomerName = $result[self::FIELD_CUSTOMER_NAME];
            $this->lengowCustomerEmail = $result[self::FIELD_CUSTOMER_EMAIL];
            $this->lengowCarrier = $result[self::FIELD_CARRIER];
            $this->lengowMethod = $result[self::FIELD_CARRIER_METHOD];
            $this->lengowTracking = $result[self::FIELD_CARRIER_TRACKING];
            $this->lengowIdRelay = $result[self::FIELD_CARRIER_RELAY_ID];
            $this->lengowSentMarketplace = (bool) $result[self::FIELD_SENT_MARKETPLACE];
            $this->lengowIsReimported = (bool) $result[self::FIELD_IS_REIMPORTED];
            $this->lengowMessage = $result[self::FIELD_MESSAGE];
            $this->lengowDateAdd = $result[self::FIELD_CREATED_AT];
            $this->lengowExtra = $result[self::FIELD_EXTRA];

            return true;
        }

        return false;
    }

    /**
     * Get PrestaShop order id
     *
     * @param string $marketplaceSku Lengow order id
     * @param string $marketplace marketplace name
     * @param string|null $marketplaceLegacy old marketplace name for v2 compatibility
     *
     * @return int|false
     */
    public static function getOrderIdFromLengowOrders(string $marketplaceSku,string $marketplace,?string $marketplaceLegacy): int|false
    {
        // v2 compatibility
        $in = (
            $marketplaceLegacy === null
            ? '\'' . pSQL(Tools::strtolower($marketplace)) . '\''
            : '\'' . pSQL(Tools::strtolower($marketplace)) . '\', \''
            . pSQL(Tools::strtolower($marketplaceLegacy)) . '\''
        );
        $query = 'SELECT `id_order`,`id_flux`
            FROM `' . _DB_PREFIX_ . 'lengow_orders`
            WHERE `' . self::FIELD_MARKETPLACE_SKU . '` = \'' . pSQL($marketplaceSku) . '\'
            AND `' . self::FIELD_MARKETPLACE_NAME . '` IN (' . $in . ')
            AND (`id_order` IS NOT NULL AND `id_order` != 0)';
        try {
            $results = Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
        if (empty($results)) {
            return false;
        }

        return (int) reset($results)['id_order'];
    }

    /**
     * Get ID record from lengow orders table
     *
     * @param string $marketplaceSku Lengow order id
     * @param string $marketplace marketplace name
     *
     * @return int|false
     */
    public static function getIdFromLengowOrders(string $marketplaceSku,string $marketplace): int|false
    {
        $query = 'SELECT `id` FROM `' . _DB_PREFIX_ . 'lengow_orders`
            WHERE `' . self::FIELD_MARKETPLACE_SKU . '` = "' . pSQL($marketplaceSku) . '"
            AND `' . self::FIELD_MARKETPLACE_NAME . '` = "' . pSQL($marketplace) . '"';

        $result = Db::getInstance()->getRow($query);
        if ($result) {
            return (int) $result[self::FIELD_ID];
        }

        return false;
    }

    /**
     * Check if a lengow order
     *
     * @param int $idOrder PrestaShop order id
     *
     * @return bool
     */
    public static function isFromLengow(int $idOrder): bool
    {
        $query = 'SELECT `marketplace_sku`
            FROM `' . _DB_PREFIX_ . 'lengow_orders`
            WHERE `id_order` = \'' . (int) $idOrder . '\'';
        try {
            $result = Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }

        return !(empty($result) || $result[0][self::FIELD_MARKETPLACE_SKU] === '');
    }

    /**
     * Get Id from Lengow delivery address id
     *
     * @param int $idOrder PrestaShop order id
     * @param int $deliveryAddressId Lengow delivery address id
     *
     * @return int|false
     */
    public static function getIdFromLengowDeliveryAddress(int $idOrder,int $deliveryAddressId): int|false
    {
        $query = 'SELECT `id` FROM `' . _DB_PREFIX_ . 'lengow_orders`
            WHERE `id_order` = \'' . (int) $idOrder . '\'
            AND `delivery_address_id` = \'' . (int) $deliveryAddressId . '\'';
        $result = Db::getInstance()->getRow($query);
        if ($result) {
            return $result[self::FIELD_ID];
        }

        return false;
    }

    /**
     * Retrieves all the order ids for an order number Lengow
     *
     * @param string $marketplaceSku Lengow order id
     * @param string $marketplace marketplace name
     *
     * @return array<int|string, mixed>
     */
    public static function getAllOrderIdsFromLengowOrder(string $marketplaceSku,string $marketplace): array
    {
        $query = 'SELECT `id_order` FROM `' . _DB_PREFIX_ . 'lengow_orders`
            WHERE `marketplace_sku` = \'' . pSQL($marketplaceSku) . '\'
            AND `marketplace_name` = \'' . pSQL(Tools::strtolower($marketplace)) . '\'
            AND `order_process_state` != \'0\'';
        try {
            return Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return [];
        }
    }

    /**
     * Retrieves all the Lengow order ids from a marketplace reference
     *
     * @param string $marketplaceSku Lengow order id
     * @param string $marketplace marketplace name
     *
     * @return array<int|string, mixed>
     */
    public static function getAllLengowOrders(string $marketplaceSku,string $marketplace): array
    {
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'lengow_orders`
            WHERE `marketplace_sku` = \'' . pSQL($marketplaceSku) . '\'
            AND `marketplace_name` = \'' . pSQL(Tools::strtolower($marketplace)) . '\'';
        try {
            return Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return [];
        }
    }

    /**
     * Update order Lengow
     *
     * @param int $id Id of the record
     * @param array<string, mixed> $params Fields update
     *
     * @return bool
     */
    public static function updateOrderLengow(int $id,array $params): bool
    {
        return Db::getInstance()->update(self::TABLE_ORDER, $params, '`id` = \'' . (int) $id . '\'');
    }

    /**
     * Update order status
     *
     * @param string $orderStateLengow marketplace state
     * @param mixed $packageData package data
     *
     * @return string|false
     *
     * @throws Exception
     */
    public function updateState(string $orderStateLengow,mixed $packageData): string|false
    {
        $orderProcessState = self::getOrderProcessState($orderStateLengow);
        $trackingNumber = !empty($packageData->delivery->trackings)
            ? (string) $packageData->delivery->trackings[0]->number
            : null;
        // update Lengow order if necessary
        $params = [];
        if ($this->lengowState !== $orderStateLengow) {
            $params[self::FIELD_ORDER_LENGOW_STATE] = pSQL($orderStateLengow);
            $params[self::FIELD_CARRIER_TRACKING] = pSQL($trackingNumber);
        }
        if ($orderProcessState === self::PROCESS_STATE_FINISH) {
            // finish actions and order log if lengow order is shipped, closed, cancel or refunded
            LengowAction::finishAllActions((int) $this->id);
            LengowOrderError::finishOrderLogs((int) $this->lengowId, LengowOrderError::TYPE_ERROR_SEND);
            if ($this->lengowProcessState !== $orderProcessState) {
                $params[self::FIELD_ORDER_PROCESS_STATE] = (int) $orderProcessState;
            }
        }
        if (!empty($params)) {
            self::updateOrderLengow((int) $this->lengowId, $params);
        }
        // get PrestaShop equivalent state id to Lengow API state
        $idOrderState = LengowMain::getOrderState($orderStateLengow);
        // if state is different between API and PrestaShop
        if ((int) $this->getCurrentState() !== $idOrderState) {
            // change state process to shipped
            if (($orderStateLengow === self::STATE_SHIPPED || $orderStateLengow === self::STATE_CLOSED)
                && (int) $this->getCurrentState() === LengowMain::getOrderState(self::STATE_ACCEPTED)
            ) {
                // create a new order history
                $history = new OrderHistory();
                $history->id_order = $this->id;
                $history->changeIdOrderState(LengowMain::getOrderState(self::STATE_SHIPPED), $this, true);
                $history->validateFields();
                $history->add();
                if ($trackingNumber !== null) {
                    $this->setWsShippingNumber($trackingNumber);
                    $this->validateFields();
                    $this->update();
                }

                return Tools::ucfirst(self::STATE_SHIPPED);
            }
            if (($orderStateLengow === self::STATE_CANCELED || $orderStateLengow === self::STATE_REFUSED)
                && (
                    (int) $this->getCurrentState() === LengowMain::getOrderState(self::STATE_ACCEPTED)
                    || (int) $this->getCurrentState() === LengowMain::getOrderState(self::STATE_SHIPPED)
                )
            ) {
                // create a new order history
                $history = new OrderHistory();
                $history->id_order = $this->id;
                $history->changeIdOrderState(LengowMain::getOrderState(self::STATE_CANCELED), $this, true);
                $history->validateFields();
                $history->add();

                return Tools::ucfirst(self::STATE_CANCELED);
            }
        }

        return false;
    }

    /**
     * Cancel and re-import order
     *
     * @return int|false
     */
    public function cancelAndreImportOrder(): int|false
    {
        if (!$this->isReimported()) {
            return false;
        }
        $import = new LengowImport(
            [
                LengowImport::PARAM_ID_ORDER_LENGOW => $this->lengowId,
                LengowImport::PARAM_MARKETPLACE_SKU => $this->lengowMarketplaceSku,
                LengowImport::PARAM_MARKETPLACE_NAME => $this->lengowMarketplaceName,
                LengowImport::PARAM_DELIVERY_ADDRESS_ID => $this->lengowDeliveryAddressId,
                LengowImport::PARAM_SHOP_ID => $this->lengowIdShop,
            ]
        );
        $result = $import->exec();
        if (!empty($result[LengowImport::ORDERS_CREATED])) {
            $orderCreated = $result[LengowImport::ORDERS_CREATED][0];
            if ($orderCreated[LengowImportOrder::MERCHANT_ORDER_ID] !== (int) $this->id) {
                $this->setStateToError();

                return (int) $orderCreated[LengowImportOrder::MERCHANT_ORDER_ID];
            }
        }
        // in the event of an error, all new order errors are finished and the order is reset
        LengowOrderError::finishOrderLogs((int) $this->lengowId);
        self::updateOrderLengow((int) $this->lengowId, [self::FIELD_IS_REIMPORTED => 0]);

        return false;
    }

    /**
     * Mark order as is_reimported in lengow_orders table
     *
     * @return bool
     */
    public function isReimported(): bool
    {
        $query = 'UPDATE ' . _DB_PREFIX_ . 'lengow_orders
            SET `is_reimported` = 1
            WHERE `id_order`= \'' . (int) $this->id . '\'';

        return Db::getInstance()->execute($query);
    }

    /**
     * Sets order state to Lengow technical error
     * @return void
     */
    public function setStateToError(): void
    {
        $idErrorLengowState = LengowMain::getLengowErrorStateId();
        // update order to Lengow error state if not already updated
        if ($idErrorLengowState && (int) $this->getCurrentState() !== $idErrorLengowState) {
            $this->setCurrentState($idErrorLengowState, Context::getContext()->employee->id);
        }
    }

    /**
     * Get all unset orders
     *
     * @return array<int|string, mixed>|false
     */
    public static function getUnsentOrders(): array|false
    {
        $date = date(LengowMain::DATE_FULL, strtotime('-5 days', time()));
        $sql = 'SELECT lo.`id`, oh.`id_order_state`, oh.`id_order`
            FROM ' . _DB_PREFIX_ . 'lengow_orders lo
            INNER JOIN ' . _DB_PREFIX_ . 'order_history oh ON (oh.id_order = lo.id_order)
            WHERE lo.`order_process_state` = ' . self::PROCESS_STATE_IMPORT
            . ' AND oh.`id_order_state` IN ('
            . LengowMain::getOrderState(self::STATE_SHIPPED) . ',' . LengowMain::getOrderState(self::STATE_CANCELED)
            . ') AND oh.`date_add` >= "' . $date . '"';
        try {
            $results = Db::getInstance()->executeS($sql);
        } catch (PrestaShopDatabaseException $e) {
            $results = false;
        }
        if ($results) {
            $unsentOrders = [];
            foreach ($results as $result) {
                $activeAction = LengowAction::getActionsByOrderId($result[self::FIELD_ORDER_ID], true);
                $orderLogs = LengowOrderError::getOrderLogs(
                    $result[self::FIELD_ID],
                    LengowOrderError::TYPE_ERROR_SEND,
                    false
                );
                if (!$activeAction
                    && !array_key_exists($result[self::FIELD_ORDER_ID], $unsentOrders)
                ) {
                    $action = (int) $result['id_order_state'] === LengowMain::getOrderState(self::STATE_CANCELED)
                        ? LengowAction::TYPE_CANCEL
                        : LengowAction::TYPE_SHIP;
                    $unsentOrders[$result[self::FIELD_ORDER_ID]] = $action;
                }
            }
            if (!empty($unsentOrders)) {
                return $unsentOrders;
            }
        }

        return false;
    }

    /**
     * Synchronize order with Lengow API
     *
     * @param LengowConnector|null $connector Lengow connector instance
     * @param bool $logOutput see log or not
     *
     * @return bool
     */
    public function synchronizeOrder(?LengowConnector $connector = null,bool $logOutput = false): bool
    {
        list($accountId, $accessToken, $secretToken) = LengowConfiguration::getAccessIds();
        // get connector
        if ($connector === null) {
            if (LengowConnector::isValidAuth($logOutput)) {
                $connector = new LengowConnector($accessToken, $secretToken);
            } else {
                return false;
            }
        }
        // get all order ids for a Lengow order
        $orderIds = self::getAllOrderIdsFromLengowOrder($this->lengowMarketplaceSku, $this->lengowMarketplaceName);
        if (empty($orderIds)) {
            return false;
        }
        $prestaIds = [];
        foreach ($orderIds as $orderId) {
            $prestaIds[] = $orderId[self::FIELD_ORDER_ID];
        }
        // compatibility V2
        if ($this->lengowIdFlux !== null) {
            $this->checkAndChangeMarketplaceName($connector, $logOutput);
        }
        $body = [
            LengowImport::ARG_ACCOUNT_ID => $accountId,
            LengowImport::ARG_MARKETPLACE_ORDER_ID => $this->lengowMarketplaceSku,
            LengowImport::ARG_MARKETPLACE => $this->lengowMarketplaceName,
            LengowImport::ARG_MERCHANT_ORDER_ID => $prestaIds,
        ];

        $tries = self::SYNCHRONIZE_TRIES;
        do {
            try {
                $result = $connector->patch(
                    LengowConnector::API_ORDER_MOI,
                    [],
                    LengowConnector::FORMAT_JSON,
                    json_encode($body),
                    $logOutput
                );

                return !($result === null
                    || (isset($result['detail']) && $result['detail'] === 'Pas trouvé.')
                    || isset($result['error']));
            } catch (Exception $e) {
                --$tries;
                if ($tries === 0) {
                    $message = LengowMain::decodeLogMessage($e->getMessage(), LengowTranslation::DEFAULT_ISO_CODE);
                    $error = LengowMain::setLogMessage(
                        'log.connector.error_api',
                        [
                            'error_code' => $e->getCode(),
                            'error_message' => $message,
                        ]
                    );
                    LengowMain::log(LengowLog::CODE_CONNECTOR, $error, $logOutput);
                }
                usleep(250000);
            }
        } while ($tries > 0);

        return false;
    }

    /**
     * Check and change the name of the marketplace for v3 compatibility
     *
     * @param LengowConnector|null $connector Lengow connector instance
     * @param bool $logOutput see log or not
     *
     * @return bool
     */
    public function checkAndChangeMarketplaceName(?LengowConnector $connector = null,bool $logOutput = false): bool
    {
        list($accountId, $accessToken, $secretToken) = LengowConfiguration::getAccessIds();
        // get connector
        if ($connector === null) {
            if (LengowConnector::isValidAuth($logOutput)) {
                $connector = new LengowConnector($accessToken, $secretToken);
            } else {
                return false;
            }
        }
        try {
            $results = $connector->get(
                LengowConnector::API_ORDER,
                [
                    LengowImport::ARG_MARKETPLACE_ORDER_ID => $this->lengowMarketplaceSku,
                    LengowImport::ARG_MARKETPLACE => $this->lengowMarketplaceName,
                    LengowImport::ARG_ACCOUNT_ID => $accountId,
                ],
                LengowConnector::FORMAT_STREAM,
                '',
                $logOutput
            );
        } catch (Exception $e) {
            $message = LengowMain::decodeLogMessage($e->getMessage(), LengowTranslation::DEFAULT_ISO_CODE);
            $error = LengowMain::setLogMessage(
                'log.connector.error_api',
                [
                    'error_code' => $e->getCode(),
                    'error_message' => $message,
                ]
            );
            LengowMain::log('Connector', $error, $logOutput);

            return false;
        }
        if ($results === null) {
            return false;
        }
        $results = json_decode($results);
        if (isset($results->error)) {
            return false;
        }
        foreach ($results->results as $order) {
            if ($this->lengowMarketplaceName !== (string) $order->marketplace) {
                $update = 'UPDATE ' . _DB_PREFIX_ . 'lengow_orders
                    SET `marketplace_name` = \'' . pSQL(Tools::strtolower((string) $order->marketplace)) . '\'
                    WHERE `id_order` = \'' . (int) $this->id . '\'
                ';
                Db::getInstance()->execute($update);
                $this->loadLengowFields();
            }
        }

        return true;
    }

    /**
     * Get PrestaShop state name
     *
     * @return string|null
     */
    public function getCurrentStateName(): ?string
    {
        try {
            $idLang = Language::getIdByIso(LengowTranslation::ISO_CODE_EN) ?: Configuration::get('PS_LANG_DEFAULT');
            $orderState = new OrderState($this->getCurrentState(), (int) $idLang);

            return $orderState->name !== '' ? $orderState->name : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get PrestaShop carrier name
     *
     * @return string|null
     */
    public function getCurrentCarrierName(): ?string
    {
        // get delivery address for carrier, shipping method and tracking url
        $deliveryAddress = new Address($this->id_address_delivery);
        $idActiveCarrier = LengowCarrier::getIdActiveCarrierByIdCarrier(
            (int) $this->id_carrier,
            (int) $deliveryAddress->id_country
        );
        $idCarrier = $idActiveCarrier ?: (int) $this->id_carrier;
        $carrier = new Carrier($idCarrier);

        return $carrier->name !== '' ? $carrier->name : null;
    }

    /**
     * Get PrestaShop tracking number
     *
     * @return string|null
     */
    public function getCurrentTrackingNumber(): ?string
    {
        try {
            $orderCarrier = new LengowOrderCarrier($this->getIdOrderCarrier());
            $trackingNumber = $orderCarrier->tracking_number;
        } catch (Exception $e) {
            $trackingNumber = '';
        }
        if ($trackingNumber === '') {
            $trackingNumber = $this->setWsShippingNumber($trackingNumber);
        }

        return $trackingNumber !== '' ? $trackingNumber : null;
    }

    /**
     * Get PrestaShop tracking url
     *
     * @return string|null
     */
    public function getCurrentTrackingUrl(): ?string
    {
        // get tracking number
        $trackingNumber = $this->getCurrentTrackingNumber();
        if ($trackingNumber === null) {
            return null;
        }
        // get delivery address for carrier, shipping method and tracking url
        $deliveryAddress = new Address($this->id_address_delivery);
        $idActiveCarrier = LengowCarrier::getIdActiveCarrierByIdCarrier(
            (int) $this->id_carrier,
            (int) $deliveryAddress->id_country
        );
        $idCarrier = $idActiveCarrier ?: (int) $this->id_carrier;
        $carrier = new Carrier($idCarrier);
        $trackingUrl = str_replace('@', $trackingNumber, $carrier->url);

        return $trackingUrl !== '' ? $trackingUrl : null;
    }

    /**
     * Check if order has an action in progress
     *
     * @return bool
     */
    public function hasAnActionInProgress(): bool
    {
        return (bool) LengowAction::getActionsByOrderId($this->id, true, null, false);
    }

    /**
     * Get order process state
     *
     * @param string $state state to be matched
     *
     * @return int|false
     */
    public static function getOrderProcessState(string $state): int|false
    {
        switch ($state) {
            case self::STATE_ACCEPTED:
            case self::STATE_WAITING_SHIPMENT:
                return self::PROCESS_STATE_IMPORT;
            case self::STATE_SHIPPED:
            case self::STATE_CLOSED:
            case self::STATE_REFUSED:
            case self::STATE_CANCELED:
            case self::STATE_REFUNDED:
                return self::PROCESS_STATE_FINISH;
            default:
                return false;
        }
    }

    /**
     * Find Lengow Order by Lengow order id
     *
     * @param int $idOrderLengow Lengow order id
     *
     * @return array<int|string, mixed>|false
     */
    public static function find(int $idOrderLengow): array|false
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'lengow_orders` WHERE id = ' . (int) $idOrderLengow;

        return Db::getInstance()->getRow($sql);
    }

    /**
     * Check if order is already imported
     *
     * @param int $idOrderLengow Lengow order id
     *
     * @return bool
     */
    public static function isOrderImport(int $idOrderLengow): bool
    {
        $sql = 'SELECT id_order FROM `' . _DB_PREFIX_ . 'lengow_orders` WHERE id = ' . (int) $idOrderLengow;
        try {
            $result = Db::getInstance()->ExecuteS($sql);

            return !empty($result);
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
    }

    /**
     * Re Import Order
     *
     * @param int $idOrderLengow Lengow order id
     *
     * @return array<int|string, mixed>|false
     */
    public static function reImportOrder(int $idOrderLengow): array|false
    {
        if (self::isOrderImport($idOrderLengow)) {
            Db::getInstance()->Execute(
                'UPDATE `' . _DB_PREFIX_ . 'lengow_orders` SET id_order = NULL WHERE id = ' . (int) $idOrderLengow
            );
            $lengowOrder = self::find($idOrderLengow);
            $import = new LengowImport(
                [
                    LengowImport::PARAM_ID_ORDER_LENGOW => $idOrderLengow,
                    LengowImport::PARAM_MARKETPLACE_SKU => $lengowOrder[self::FIELD_MARKETPLACE_SKU],
                    LengowImport::PARAM_MARKETPLACE_NAME => $lengowOrder[self::FIELD_MARKETPLACE_NAME],
                    LengowImport::PARAM_DELIVERY_ADDRESS_ID => $lengowOrder[self::FIELD_DELIVERY_ADDRESS_ID],
                    LengowImport::PARAM_SHOP_ID => $lengowOrder[self::FIELD_SHOP_ID],
                ]
            );

            return $import->exec();
        }

        return false;
    }

    /**
     * Check if can resend action order
     *
     * @return bool
     */
    public function canReSendOrder(): bool
    {
        $orderActions = LengowAction::getActionsByOrderId((int) $this->id, true);
        if ($orderActions) {
            return false;
        }
        if ($this->lengowProcessState !== self::PROCESS_STATE_FINISH
            && (
                (int) $this->getCurrentState() === LengowMain::getOrderState(self::STATE_SHIPPED)
                || (int) $this->getCurrentState() === LengowMain::getOrderState(self::STATE_CANCELED)
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Re Send Order
     *
     * @param int $idOrderLengow Lengow order id
     *
     * @return bool
     */
    public static function reSendOrder(int $idOrderLengow): bool
    {
        if (self::isOrderImport($idOrderLengow)) {
            $lengowOrder = self::find($idOrderLengow);
            if ((int) $lengowOrder[self::FIELD_ORDER_ID] > 0) {
                $order = new LengowOrder($lengowOrder[self::FIELD_ORDER_ID]);
                $action = LengowAction::getLastOrderActionType($lengowOrder[self::FIELD_ORDER_ID]);
                if (!$action) {
                    $action = (int) $order->getCurrentState() === LengowMain::getOrderState(self::STATE_CANCELED)
                        ? LengowAction::TYPE_CANCEL
                        : LengowAction::TYPE_SHIP;
                }

                return $order->callAction($action);
            }

            return false;
        }

        return false;
    }

    /**
     * will search the marketplace linked to the order
     *
     * @return LengowMarketplace|null
     */
    public function getMarketPlace(): ?LengowMarketplace
    {
        return LengowMain::getMarketplaceSingleton(
            $this->lengowMarketplaceName
        );
    }

    /**
     * Send Order action
     *
     * @param string $action Lengow Actions type (ship or cancel)
     *
     * @return bool
     * @param mixed $partialAction
     */
    public function callAction(string $action,mixed $partialAction = false): bool
    {
        $success = true;
        LengowMain::log(
            'API-OrderAction',
            LengowMain::setLogMessage(
                'log.order_action.try_to_send_action',
                [
                    'action' => $action,
                    'order_id' => $this->id,
                ]
            ),
            false,
            $this->lengowMarketplaceSku
        );
        if ((int) $this->id === 0) {
            LengowMain::log(
                'API-OrderAction',
                LengowMain::setLogMessage('log.order_action.can_not_load_order'),
                true
            );
            $success = false;
        }
        if ($success) {
            // finish all order logs send
            LengowOrderError::finishOrderLogs((int) $this->lengowId, LengowOrderError::TYPE_ERROR_SEND);
            try {
                // compatibility V2
                if ($this->lengowIdFlux !== null) {
                    $this->checkAndChangeMarketplaceName();
                }
                $marketplace = LengowMain::getMarketplaceSingleton($this->lengowMarketplaceName);
                if (is_null($marketplace)) {
                    throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.marketplace_not_present', ['marketplace_name' => $this->lengowMarketplaceName]));
                }
                if ($marketplace->containOrderLine($action)) {
                    $orderLineCollection = LengowOrderLine::findOrderLineIds($this->id);
                    // compatibility V2 and security
                    if (empty($orderLineCollection)) {
                        $orderLineCollection = $this->getOrderLineByApi();
                    }
                    if (!$orderLineCollection) {
                        throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.order_line_required'));
                    }
                    $results = [];

                    foreach ($orderLineCollection as $row) {
                        $results[] = $marketplace->callAction(
                            $action,
                            $this,
                            $row[LengowOrderLine::FIELD_ORDER_LINE_ID],
                            $partialAction
                        );
                    }
                    $success = !in_array(false, $results, true);
                } else {
                    $success = $marketplace->callAction($action, $this);
                }
            } catch (LengowException $e) {
                $errorMessage = $e->getMessage();
            } catch (Exception $e) {
                $errorMessage = '[PrestaShop error]: "' . $e->getMessage()
                    . '" ' . $e->getFile() . ' | ' . $e->getLine();
            }
            if (isset($errorMessage)) {
                if ($this->lengowProcessState !== self::PROCESS_STATE_FINISH) {
                    LengowOrderError::addOrderLog((int) $this->lengowId, $errorMessage, LengowOrderError::TYPE_ERROR_SEND);
                }
                $decodedMessage = LengowMain::decodeLogMessage($errorMessage, LengowTranslation::DEFAULT_ISO_CODE);
                LengowMain::log(
                    'API-OrderAction',
                    LengowMain::setLogMessage(
                        'log.order_action.call_action_failed',
                        ['decoded_message' => $decodedMessage]
                    ),
                    false,
                    $this->lengowMarketplaceSku
                );
                $success = false;
            }
        }
        if ($success) {
            $message = LengowMain::setLogMessage(
                'log.order_action.action_send',
                [
                    'action' => $action,
                    'order_id' => $this->id,
                ]
            );
        } else {
            $message = LengowMain::setLogMessage(
                'log.order_action.action_not_send',
                [
                    'action' => $action,
                    'order_id' => $this->id,
                ]
            );
        }
        LengowMain::log('API-OrderAction', $message, false, $this->lengowMarketplaceSku);

        return $success;
    }

    /**
     * Get order line by API
     *
     * @return array<int|string, mixed>|false
     */
    public function getOrderLineByApi(): array|false
    {
        $orderLines = [];
        $results = LengowConnector::queryApi(
            LengowConnector::GET,
            LengowConnector::API_ORDER,
            [
                LengowImport::ARG_MARKETPLACE_ORDER_ID => $this->lengowMarketplaceSku,
                LengowImport::ARG_MARKETPLACE => $this->lengowMarketplaceName,
            ]
        );
        if (isset($results->count) && (int) $results->count === 0) {
            return false;
        }
        $orderData = $results->results[0];
        foreach ($orderData->packages as $package) {
            $productLines = [];
            foreach ($package->cart as $product) {
                $productLines[] = [
                    LengowOrderLine::FIELD_ORDER_LINE_ID => (string) $product->marketplace_order_line_id,
                ];
            }
            if ($this->lengowDeliveryAddressId === 0) {
                return !empty($productLines) ? $productLines : false;
            }
            $orderLines[(int) $package->delivery->id] = $productLines;
        }
        $return = $orderLines[$this->lengowDeliveryAddressId];

        return !empty($return) ? $return : false;
    }

    /**
     * Check if order is express
     *
     * @return bool
     */
    public function isExpress(): bool
    {
        return isset($this->lengowOrderTypes[self::TYPE_EXPRESS]) || isset($this->lengowOrderTypes[self::TYPE_PRIME]);
    }

    /**
     * Check if order is B2B
     *
     * @return bool
     */
    public function isBusiness(): bool
    {
        if (isset($this->lengowOrderTypes[self::TYPE_BUSINESS])) {
            return true;
        }
        $extraData = json_decode($this->lengowExtra, true);
        $paymentInfo = $extraData['payments'][0] ?? [];
        $billingInfo = $extraData['billing_address'] ?? [];

        if (isset($paymentInfo['payment_terms'])) {
            $fiscalNumber = $paymentInfo['payment_terms']['fiscalnb'] ?? '';
            $vatNumber = $paymentInfo['payment_terms']['vat_number'] ?? '';
            $siretNumber = $paymentInfo['payment_terms']['siret_number'] ?? '';

            if (!empty($fiscalNumber)
                    || !empty($vatNumber)
                    || !empty($siretNumber)) {
                $this->lengowOrderTypes[self::TYPE_BUSINESS] = self::FIELD_B2B_VALUE;

                return true;
            }
        }
        if (!empty($billingInfo['vat_number'])
            && !empty($billingInfo['company'])) {
            $this->lengowOrderTypes[self::TYPE_BUSINESS] = self::FIELD_B2B_VALUE;

            return true;
        }

        return false;
    }

    /**
     * Check if order is delivered by marketplace
     *
     * @return bool
     */
    public function isDeliveredByMarketplace(): bool
    {
        return isset($this->lengowOrderTypes[self::TYPE_DELIVERED_BY_MARKETPLACE]) || $this->lengowSentMarketplace;
    }

    /**
     * Return the number of Lengow orders imported in PrestaShop
     *
     * @return int
     */
    public static function countOrderImportedByLengow(): int
    {
        $sql = 'SELECT COUNT(*) as total FROM ' . _DB_PREFIX_ . 'lengow_orders WHERE id_order IS NOT NULL';
        $row = Db::getInstance()->getRow($sql);

        return (int) $row['total'];
    }

    /**
     * Return the number of Lengow orders with error
     *
     * @return int
     */
    public static function countOrderWithError(): int
    {
        $sql = '
            SELECT COUNT(DISTINCT lo.id) as total FROM ' . _DB_PREFIX_ . 'lengow_orders as lo
            LEFT JOIN ' . _DB_PREFIX_ . 'lengow_logs_import as lli ON lli.id_order_lengow = lo.id
            WHERE lli.is_finished = 0
        ';
        $row = Db::getInstance()->getRow($sql);

        return (int) $row['total'];
    }

    /**
     * Return the number of Lengow orders to be sent
     *
     * @return int
     */
    public static function countOrderToBeSent(): int
    {
        $sql = 'SELECT COUNT(*) as total FROM ' . _DB_PREFIX_ . 'lengow_orders WHERE order_process_state = 1';
        $row = Db::getInstance()->getRow($sql);

        return (int) $row['total'];
    }

    /**
     * Get the refund reason from the Lengow orders table
     *
     * @param int $idOrder PrestaShop order id
     *
     * @return string|null
     */
    public static function getRefundReasonByPrestashopId(int $idOrder): ?string
    {
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'lengow_orders` WHERE `id` = ' . (int) $idOrder;
        try {
            $result = Db::getInstance()->getRow($query);
        } catch (PrestaShopDatabaseException $e) {
            return null;
        }

        return $result['refund_reason'] ?? null;
    }

    /**
     * Get the refund mode from the Lengow orders table
     *
     * @param int $idOrder PrestaShop order id
     *
     * @return string|null
     */
    public static function getRefundModeByPrestashopId(int $idOrder): ?string
    {
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'lengow_orders` WHERE `id` = ' . (int) $idOrder;
        try {
            $result = Db::getInstance()->getRow($query);
        } catch (PrestaShopDatabaseException $e) {
            return null;
        }

        return $result['refund_mode'] ?? null;
    }

    /**
     * Get the total shipping cost amount for a specific order ID
     *
     * @param int $orderId The ID of the order
     *
     * @return float|false Returns the total shipping cost amount or false on failure
     */
    public static function getTotalShippingCostByOrderId(int $orderId): float|false
    {
        $query = 'SELECT SUM(os.shipping_cost_amount) AS total_shipping_cost
              FROM `' . _DB_PREFIX_ . 'order_slip` os
              WHERE os.id_order = ' . (int) $orderId;

        try {
            $result = Db::getInstance()->getRow($query);

            return $result ? (float) $result['total_shipping_cost'] : 0.0;
        } catch (PrestaShopDatabaseException $e) {
            return 0;
        }
    }

    /**
     * @param int $orderId
     * @param string $marketplaceName
     * @return array<string, mixed>
     */
    public function getRefundDataFromLengowOrder(int $orderId,string $marketplaceName): array
    {
        $db = Db::getInstance();
        $sql = 'SELECT refund_reason, refund_mode
            FROM ' . _DB_PREFIX_ . 'lengow_orders
            WHERE id_order = ' . (int) $orderId . '
            AND marketplace_name = "' . pSQL($marketplaceName) . '"';

        $result = $db->getRow($sql);

        if (!$result) {
            return [
                'refund_reason' => [],
                'refund_mode' => [],
            ];
        }

        return [
            'refund_reason' => $result['refund_reason'] ?? [],
            'refund_mode' => $result['refund_mode'] ?? [],
        ];
    }

    /**
     * Return the Lengow orders from PrestaShop order id
     *
     * @param int $idOrder PrestaShop order id
     *
     * @return array<int|string, mixed>|null
     */
    public static function getLengowOrderByPrestashopId(int $idOrder): ?array
    {
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'lengow_orders` WHERE `id_order` = ' . (int) $idOrder;
        try {
            $result = Db::getInstance()->getRow($query);
        } catch (PrestaShopDatabaseException $e) {
            return null;
        }

        return $result ?: null;
    }

    /**
     * Get the shipping method from the Lengow orders table
     *
     * @param int $idOrder PrestaShop order id
     *
     * @return string|null
     */
    public static function getShippingMethodByPrestashopId(int $idOrder): ?string
    {
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'lengow_orders` WHERE `id` = ' . (int) $idOrder;
        try {
            $result = Db::getInstance()->getRow($query);
        } catch (PrestaShopDatabaseException $e) {
            return null;
        }

        return $result['method'] ?? null;
    }
}

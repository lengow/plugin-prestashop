<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowMarketplaceTest extends TestCase
{
    /**
     * @var \LengowMarketplace
     */
    protected $marketplace;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->marketplace = new \LengowMarketplace(
            'amazon_fr',
            $this->getMarketplacesMock()
        );
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowMarketplace::class,
            $this->marketplace,
            '[Test Class Instantiation] Check class instantiation'
        );
    }

    /**
     * @return string
     */
    protected function getMarketplacesMock()
    {
        return '{
            "amazon_fr": {
                "logo": null,
                "name": "Amazon FR",
                "orders": {
                    "status": {
                        "new": ["Pending", "PendingAvailability"],
                        "shipped": ["Shipped", "InvoiceUnconfirmed"],
                        "canceled": ["Canceled"],
                        "refunded": ["Refunded"],
                        "waiting_shipment": ["PartiallyShipped", "Unfulfillable", "Unshipped"]
                    },
                    "actions": {
                        "ship": {
                            "args": ["carrier", "shipping_method"],
                            "status": ["waiting_shipment"],
                            "optional_args": ["carrier_name", "line", "shipping_date", "tracking_number"],
                            "args_description": {
                                "line": {
                                    "type": "line_number",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "carrier": {
                                    "type": "list",
                                    "depends_on": null,
                                    "valid_values": {
                                        "4PX": {
                                            "label": "4PX"
                                        },
                                        "A-1": {
                                            "label": "A-1"
                                        },
                                        "ABF": {
                                            "label": "ABF"
                                        },
                                        "ATS": {
                                            "label": "ATS"
                                        },
                                        "BJS": {
                                            "label": "BJS"
                                        },
                                        "BRT": {
                                            "label": "BRT"
                                        },
                                        "CDC": {
                                            "label": "CDC"
                                        },
                                        "DHL": {
                                            "label": "DHL"
                                        },
                                        "DPD": {
                                            "label": "DPD"
                                        },
                                        "DSV": {
                                            "label": "DSV"
                                        },
                                        "EUB": {
                                            "label": "EUB"
                                        },
                                        "GLS": {
                                            "label": "GLS"
                                        },
                                        "GO!": {
                                            "label": "GO!"
                                        },
                                        "MRW": {
                                            "label": "MRW"
                                        },
                                        "OSM": {
                                            "label": "OSM"
                                        },
                                        "R+L": {
                                            "label": "R+L"
                                        },
                                        "SDA": {
                                            "label": "SDA"
                                        },
                                        "SFC": {
                                            "label": "SFC"
                                        },
                                        "TNT": {
                                            "label": "TNT"
                                        },
                                        "UPS": {
                                            "label": "UPS"
                                        },
                                        "VIR": {
                                            "label": "VIR"
                                        },
                                        "XDP": {
                                            "label": "XDP"
                                        },
                                        "YDH": {
                                            "label": "YDH"
                                        },
                                        "CEVA": {
                                            "label": "CEVA"
                                        },
                                        "DTDC": {
                                            "label": "DTDC"
                                        },
                                        "ECMS": {
                                            "label": "ECMS"
                                        },
                                        "Gati": {
                                            "label": "Gati"
                                        },
                                        "JCEX": {
                                            "label": "JCEX"
                                        },
                                        "Otro": {
                                            "label": "Otro"
                                        },
                                        "Saia": {
                                            "label": "Saia"
                                        },
                                        "Seur": {
                                            "label": "Seur"
                                        },
                                        "USPS": {
                                            "label": "USPS"
                                        },
                                        "Arkas": {
                                            "label": "Arkas"
                                        },
                                        "DHLPL": {
                                            "label": "DHLPL"
                                        },
                                        "Estes": {
                                            "label": "Estes"
                                        },
                                        "FedEx": {
                                            "label": "FedEx"
                                        },
                                        "Nacex": {
                                            "label": "Nacex"
                                        },
                                        "Pilot": {
                                            "label": "Pilot"
                                        },
                                        "Rieck": {
                                            "label": "Rieck"
                                        },
                                        "Seino": {
                                            "label": "Seino"
                                        },
                                        "TIPSA": {
                                            "label": "TIPSA"
                                        },
                                        "TNTIT": {
                                            "label": "TNTIT"
                                        },
                                        "UPSMI": {
                                            "label": "UPSMI"
                                        },
                                        "VNLIN": {
                                            "label": "VNLIN"
                                        },
                                        "WINIT": {
                                            "label": "WINIT"
                                        },
                                        "Yodel": {
                                            "label": "Yodel"
                                        },
                                        "ALLJOY": {
                                            "label": "ALLJOY"
                                        },
                                        "Aramex": {
                                            "label": "Aramex"
                                        },
                                        "Asgard": {
                                            "label": "Asgard"
                                        },
                                        "Assett": {
                                            "label": "Assett"
                                        },
                                        "Balnak": {
                                            "label": "Balnak"
                                        },
                                        "Bombax": {
                                            "label": "Bombax"
                                        },
                                        "Conway": {
                                            "label": "Conway"
                                        },
                                        "Dotzot": {
                                            "label": "Dotzot"
                                        },
                                        "Energo": {
                                            "label": "Energo"
                                        },
                                        "InPost": {
                                            "label": "InPost"
                                        },
                                        "NITTSU": {
                                            "label": "NITTSU"
                                        },
                                        "Nexive": {
                                            "label": "Nexive"
                                        },
                                        "OnTrac": {
                                            "label": "OnTrac"
                                        },
                                        "Rhenus": {
                                            "label": "Rhenus"
                                        },
                                        "Rivigo": {
                                            "label": "Rivigo"
                                        },
                                        "SAGAWA": {
                                            "label": "SAGAWA"
                                        },
                                        "SENDLE": {
                                            "label": "SENDLE"
                                        },
                                        "Sendle": {
                                            "label": "Sendle"
                                        },
                                        "Spoton": {
                                            "label": "Spoton"
                                        },
                                        "Target": {
                                            "label": "Target"
                                        },
                                        "YAMATO": {
                                            "label": "YAMATO"
                                        },
                                        "YANWEN": {
                                            "label": "YANWEN"
                                        },
                                        "geodis": {
                                            "label": "geodis"
                                        },
                                        "AT POST": {
                                            "label": "AT POST"
                                        },
                                        "Asendia": {
                                            "label": "Asendia"
                                        },
                                        "Correos": {
                                            "label": "Correos"
                                        },
                                        "DACHSER": {
                                            "label": "DACHSER"
                                        },
                                        "Fastway": {
                                            "label": "Fastway"
                                        },
                                        "HS code": {
                                            "label": "HS code"
                                        },
                                        "Heppner": {
                                            "label": "Heppner"
                                        },
                                        "Milkman": {
                                            "label": "Milkman"
                                        },
                                        "Panther": {
                                            "label": "Panther"
                                        },
                                        "Post NL": {
                                            "label": "Post NL"
                                        },
                                        "Qxpress": {
                                            "label": "Qxpress"
                                        },
                                        "Speedex": {
                                            "label": "Speedex"
                                        },
                                        "Trackon": {
                                            "label": "Trackon"
                                        },
                                        "iParcel": {
                                            "label": "iParcel"
                                        },
                                        "Arrow XL": {
                                            "label": "Arrow XL"
                                        },
                                        "Best Buy": {
                                            "label": "Best Buy"
                                        },
                                        "BlueDart": {
                                            "label": "BlueDart"
                                        },
                                        "Correios": {
                                            "label": "Correios"
                                        },
                                        "Endopack": {
                                            "label": "Endopack"
                                        },
                                        "Envialia": {
                                            "label": "Envialia"
                                        },
                                        "Estafeta": {
                                            "label": "Estafeta"
                                        },
                                        "FEDEX_JP": {
                                            "label": "FEDEX_JP"
                                        },
                                        "Kargokar": {
                                            "label": "Kargokar"
                                        },
                                        "La Poste": {
                                            "label": "La Poste"
                                        },
                                        "Landmark": {
                                            "label": "Landmark"
                                        },
                                        "Ninjavan": {
                                            "label": "Ninjavan"
                                        },
                                        "PostNord": {
                                            "label": "PostNord"
                                        },
                                        "QExpress": {
                                            "label": "QExpress"
                                        },
                                        "Tourline": {
                                            "label": "Tourline"
                                        },
                                        "Whizzard": {
                                            "label": "Whizzard"
                                        },
                                        "AFL\/Fedex": {
                                            "label": "AFL\/Fedex"
                                        },
                                        "CELERITAS": {
                                            "label": "CELERITAS"
                                        },
                                        "Cititrans": {
                                            "label": "Cititrans"
                                        },
                                        "City Link": {
                                            "label": "City Link"
                                        },
                                        "Coliposte": {
                                            "label": "Coliposte"
                                        },
                                        "Colissimo": {
                                            "label": "Colissimo"
                                        },
                                        "DHL Kargo": {
                                            "label": "DHL Kargo"
                                        },
                                        "DHL-Paket": {
                                            "label": "DHL-Paket"
                                        },
                                        "Delhivery": {
                                            "label": "Delhivery"
                                        },
                                        "DirectLog": {
                                            "label": "DirectLog"
                                        },
                                        "Lasership": {
                                            "label": "Lasership"
                                        },
                                        "MNG Kargo": {
                                            "label": "MNG Kargo"
                                        },
                                        "PTT Kargo": {
                                            "label": "PTT Kargo"
                                        },
                                        "PUROLATOR": {
                                            "label": "PUROLATOR"
                                        },
                                        "Parcelnet": {
                                            "label": "Parcelnet"
                                        },
                                        "Smartmail": {
                                            "label": "Smartmail"
                                        },
                                        "TNT Kargo": {
                                            "label": "TNT Kargo"
                                        },
                                        "Tuffnells": {
                                            "label": "Tuffnells"
                                        },
                                        "AAA Cooper": {
                                            "label": "AAA Cooper"
                                        },
                                        "Aras Kargo": {
                                            "label": "Aras Kargo"
                                        },
                                        "Bo\u011fazi\u00e7i": {
                                            "label": "Bo\u011fazi\u00e7i"
                                        },
                                        "CTTExpress": {
                                            "label": "CTTExpress"
                                        },
                                        "Cart2India": {
                                            "label": "Cart2India"
                                        },
                                        "China Post": {
                                            "label": "China Post"
                                        },
                                        "Chronopost": {
                                            "label": "Chronopost"
                                        },
                                        "DX Freight": {
                                            "label": "DX Freight"
                                        },
                                        "First Mile": {
                                            "label": "First Mile"
                                        },
                                        "India Post": {
                                            "label": "India Post"
                                        },
                                        "JP_EXPRESS": {
                                            "label": "JP_EXPRESS"
                                        },
                                        "Japan Post": {
                                            "label": "Japan Post"
                                        },
                                        "Newgistics": {
                                            "label": "Newgistics"
                                        },
                                        "Roadrunner": {
                                            "label": "Roadrunner"
                                        },
                                        "Royal Mail": {
                                            "label": "Royal Mail"
                                        },
                                        "SF Express": {
                                            "label": "SF Express"
                                        },
                                        "Safexpress": {
                                            "label": "Safexpress"
                                        },
                                        "ShipGlobal": {
                                            "label": "ShipGlobal"
                                        },
                                        "Spring GDS": {
                                            "label": "Spring GDS"
                                        },
                                        "Streamlite": {
                                            "label": "Streamlite"
                                        },
                                        "TransFolha": {
                                            "label": "TransFolha"
                                        },
                                        "Xpressbees": {
                                            "label": "Xpressbees"
                                        },
                                        "AUSSIE_POST": {
                                            "label": "AUSSIE_POST"
                                        },
                                        "COLIS PRIVE": {
                                            "label": "COLIS PRIVE"
                                        },
                                        "Canada Post": {
                                            "label": "Canada Post"
                                        },
                                        "Colis Prive": {
                                            "label": "Colis Prive"
                                        },
                                        "DB Schenker": {
                                            "label": "DB Schenker"
                                        },
                                        "DHL Express": {
                                            "label": "DHL Express"
                                        },
                                        "DHL Freight": {
                                            "label": "DHL Freight"
                                        },
                                        "Fillo Kargo": {
                                            "label": "Fillo Kargo"
                                        },
                                        "GEL Express": {
                                            "label": "GEL Express"
                                        },
                                        "Metro Kargo": {
                                            "label": "Metro Kargo"
                                        },
                                        "Parcelforce": {
                                            "label": "Parcelforce"
                                        },
                                        "Polish Post": {
                                            "label": "Polish Post"
                                        },
                                        "Raben Group": {
                                            "label": "Raben Group"
                                        },
                                        "STO Express": {
                                            "label": "STO Express"
                                        },
                                        "Selem Kargo": {
                                            "label": "Selem Kargo"
                                        },
                                        "ShipEconomy": {
                                            "label": "ShipEconomy"
                                        },
                                        "UPS Freight": {
                                            "label": "UPS Freight"
                                        },
                                        "WanbExpress": {
                                            "label": "WanbExpress"
                                        },
                                        "XPO Freight": {
                                            "label": "XPO Freight"
                                        },
                                        "YTO Express": {
                                            "label": "YTO Express"
                                        },
                                        "Yun Express": {
                                            "label": "Yun Express"
                                        },
                                        "ZTO Express": {
                                            "label": "ZTO Express"
                                        },
                                        "Best Express": {
                                            "label": "Best Express"
                                        },
                                        "Blue Package": {
                                            "label": "Blue Package"
                                        },
                                        "Ecom Express": {
                                            "label": "Ecom Express"
                                        },
                                        "First Flight": {
                                            "label": "First Flight"
                                        },
                                        "IDS Netzwerk": {
                                            "label": "IDS Netzwerk"
                                        },
                                        "Kuehne+Nagel": {
                                            "label": "Kuehne+Nagel"
                                        },
                                        "Old Dominion": {
                                            "label": "Old Dominion"
                                        },
                                        "Professional": {
                                            "label": "Professional"
                                        },
                                        "Ship Delight": {
                                            "label": "Ship Delight"
                                        },
                                        "S\u00fcrat Kargo": {
                                            "label": "S\u00fcrat Kargo"
                                        },
                                        "Ceva Lojistik": {
                                            "label": "Ceva Lojistik"
                                        },
                                        "DHL eCommerce": {
                                            "label": "DHL eCommerce"
                                        },
                                        "Deutsche Post": {
                                            "label": "Deutsche Post"
                                        },
                                        "Emirates Post": {
                                            "label": "Emirates Post"
                                        },
                                        "Fedex Freight": {
                                            "label": "Fedex Freight"
                                        },
                                        "Geopost Kargo": {
                                            "label": "Geopost Kargo"
                                        },
                                        "Hongkong Post": {
                                            "label": "Hongkong Post"
                                        },
                                        "ICC Worldwide": {
                                            "label": "ICC Worldwide"
                                        },
                                        "Narpost Kargo": {
                                            "label": "Narpost Kargo"
                                        },
                                        "NipponExpress": {
                                            "label": "NipponExpress"
                                        },
                                        "Pilot Freight": {
                                            "label": "Pilot Freight"
                                        },
                                        "SagawaExpress": {
                                            "label": "SagawaExpress"
                                        },
                                        "Self Delivery": {
                                            "label": "Self Delivery"
                                        },
                                        "Total Express": {
                                            "label": "Total Express"
                                        },
                                        "Urban Express": {
                                            "label": "Urban Express"
                                        },
                                        "Yunda Express": {
                                            "label": "Yunda Express"
                                        },
                                        "Australia Post": {
                                            "label": "Australia Post"
                                        },
                                        "Chrono Express": {
                                            "label": "Chrono Express"
                                        },
                                        "CouriersPlease": {
                                            "label": "CouriersPlease"
                                        },
                                        "Home Logistics": {
                                            "label": "Home Logistics"
                                        },
                                        "Horoz Lojistik": {
                                            "label": "Horoz Lojistik"
                                        },
                                        "Poste Italiane": {
                                            "label": "Poste Italiane"
                                        },
                                        "Ship Global US": {
                                            "label": "Ship Global US"
                                        },
                                        "Singapore Post": {
                                            "label": "Singapore Post"
                                        },
                                        "Tezel Lojistik": {
                                            "label": "Tezel Lojistik"
                                        },
                                        "Yellow Freight": {
                                            "label": "Yellow Freight"
                                        },
                                        "Yurti\u00e7i Kargo": {
                                            "label": "Yurti\u00e7i Kargo"
                                        },
                                        "Amazon Shipping": {
                                            "label": "Amazon Shipping"
                                        },
                                        "Correos Express": {
                                            "label": "Correos Express"
                                        },
                                        "Couriers Please": {
                                            "label": "Couriers Please"
                                        },
                                        "DHL Global Mail": {
                                            "label": "DHL Global Mail"
                                        },
                                        "FedEx SmartPost": {
                                            "label": "FedEx SmartPost"
                                        },
                                        "OneWorldExpress": {
                                            "label": "OneWorldExpress"
                                        },
                                        "YamatoTransport": {
                                            "label": "YamatoTransport"
                                        },
                                        "Digital Delivery": {
                                            "label": "Digital Delivery"
                                        },
                                        "Geodis Calberson": {
                                            "label": "Geodis Calberson"
                                        },
                                        "Hunter Logistics": {
                                            "label": "Hunter Logistics"
                                        },
                                        "Overnite Express": {
                                            "label": "Overnite Express"
                                        },
                                        "Shunfeng Express": {
                                            "label": "Shunfeng Express"
                                        },
                                        "DHL Home Delivery": {
                                            "label": "DHL Home Delivery"
                                        },
                                        "First Flight China": {
                                            "label": "First Flight China"
                                        },
                                        "StarTrack-ArticleID": {
                                            "label": "StarTrack-ArticleID"
                                        },
                                        "Toll Global Express": {
                                            "label": "Toll Global Express"
                                        },
                                        "Watkins and Shepard": {
                                            "label": "Watkins and Shepard"
                                        },
                                        "SEINO TRANSPORTATION": {
                                            "label": "SEINO TRANSPORTATION"
                                        },
                                        "Shree Maruti Courier": {
                                            "label": "Shree Maruti Courier"
                                        },
                                        "UPS Mail Innovations": {
                                            "label": "UPS Mail Innovations"
                                        },
                                        "StarTrack-Consignment": {
                                            "label": "StarTrack-Consignment"
                                        },
                                        "Hermes Logistik Gruppe": {
                                            "label": "Hermes Logistik Gruppe"
                                        },
                                        "Shree Tirupati Courier": {
                                            "label": "Shree Tirupati Courier"
                                        },
                                        "AustraliaPost-ArticleId": {
                                            "label": "AustraliaPost-ArticleId"
                                        },
                                        "Beijing Quanfeng Express": {
                                            "label": "Beijing Quanfeng Express"
                                        },
                                        "AustraliaPost-Consignment": {
                                            "label": "AustraliaPost-Consignment"
                                        },
                                        "The Professional Couriers": {
                                            "label": "The Professional Couriers"
                                        },
                                        "Hermes Einrichtungsservice": {
                                            "label": "Hermes Einrichtungsservice"
                                        },
                                        "South Eastern Freight Lines": {
                                            "label": "South Eastern Freight Lines"
                                        }
                                    },
                                    "default_value": "",
                                    "accept_free_values": true
                                },
                                "carrier_name": {
                                    "type": "string",
                                    "depends_on": {
                                        "operation": "allOf",
                                        "conditions": [{
                                                "value": "Other",
                                                "function": "equals",
                                                "key_path": {
                                                    "path": "carrier",
                                                    "root": "action_data"
                                                }
                                            }]
                                    },
                                    "valid_values": {},
                                    "default_value": "",
                                    "accept_free_values": true
                                },
                                "shipping_date": {
                                    "type": "date",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "shipping_method": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "tracking_number": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                }
                            }
                        },
                        "cancel": {
                            "args": [],
                            "status": ["new", "waiting_shipment"],
                            "optional_args": ["line", "reason"],
                            "args_description": {
                                "line": {
                                    "type": "line_number",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "reason": {
                                    "type": "list",
                                    "depends_on": null,
                                    "valid_values": {
                                        "NoInventory": {
                                            "label": "No inventory"
                                        },
                                        "BuyerCanceled": {
                                            "label": "Buyer canceled"
                                        },
                                        "CustomerReturn": {
                                            "label": "Customer return"
                                        },
                                        "CustomerExchange": {
                                            "label": "Customer exchange"
                                        },
                                        "GeneralAdjustment": {
                                            "label": "General adjustment"
                                        },
                                        "CarrierCreditDecision": {
                                            "label": "Carrier credit decision"
                                        },
                                        "CarrierCoverageFailure": {
                                            "label": "Carrier coverage failure"
                                        },
                                        "MerchandiseNotReceived": {
                                            "label": "Merchandise not received"
                                        },
                                        "ShippingAddressUndeliverable": {
                                            "label": "Shipping address undeliverable"
                                        },
                                        "RiskAssessmentInformationNotValid": {
                                            "label": "Risk assessment information not valid"
                                        }
                                    },
                                    "default_value": null,
                                    "accept_free_values": false
                                }
                            }
                        },
                        "refund": {
                            "args": ["line", "reason"],
                            "status": ["shipped"],
                            "optional_args": ["refund_price", "refund_shipping_price", "refund_shipping_taxes", "refund_taxes"],
                            "args_description": {
                                "line": {
                                    "type": "line_number",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "reason": {
                                    "type": "list",
                                    "depends_on": null,
                                    "valid_values": {
                                        "Other": {
                                            "label": "Other"
                                        },
                                        "Exchange": {
                                            "label": "Exchange"
                                        },
                                        "PriceError": {
                                            "label": "Price Error"
                                        },
                                        "NoInventory": {
                                            "label": "No inventory"
                                        },
                                        "CouldNotShip": {
                                            "label": "Could not ship"
                                        },
                                        "CustomerReturn": {
                                            "label": "Customer return"
                                        },
                                        "GeneralAdjustment": {
                                            "label": "General adjustment"
                                        },
                                        "ProductOutofStock": {
                                            "label": "Product out of stock"
                                        },
                                        "TransactionRecord": {
                                            "label": "Transaction record"
                                        },
                                        "CarrierCreditDecision": {
                                            "label": "Carrier credit decision"
                                        },
                                        "CarrierCoverageFailure": {
                                            "label": "Carrier coverage failure"
                                        },
                                        "CustomerAddressIncorrect": {
                                            "label": "Customer address incorrect"
                                        },
                                        "RiskAssessmentInformationNotValid": {
                                            "label": "Risk assessment information not valid"
                                        }
                                    },
                                    "default_value": "ProductOutofStock",
                                    "accept_free_values": false
                                },
                                "refund_price": {
                                    "type": "price",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "refund_taxes": {
                                    "type": "price",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "refund_shipping_price": {
                                    "type": "price",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "refund_shipping_taxes": {
                                    "type": "price",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                }
                            }
                        },
                        "buy_shipment_label": {
                            "args": ["carrier_pickup", "currency_code", "declared_value", "delivery_experience", "from_address_city", "from_address_country_code", "from_address_line", "from_address_mail", "from_address_name", "from_address_phone", "from_address_postal_code", "package_dimension_height", "package_dimension_length", "package_dimension_unit", "package_dimension_width", "shipping_date", "shipping_service_id", "weight", "weight_unit"],
                            "status": [],
                            "optional_args": ["carrier", "delivery_date", "from_address_state_province", "line", "tracking_number"],
                            "args_description": {
                                "line": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "weight": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "carrier": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "weight_unit": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "currency_code": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "delivery_date": {
                                    "type": "date",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "shipping_date": {
                                    "type": "date",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "carrier_pickup": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "declared_value": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "tracking_number": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_city": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_line": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_mail": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_name": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_phone": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "delivery_experience": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "shipping_service_id": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "package_dimension_unit": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "package_dimension_width": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_postal_code": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "package_dimension_height": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "package_dimension_length": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_country_code": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_state_province": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                }
                            }
                        },
                        "set_eligible_shipping_methods": {
                            "args": ["carrier_pickup", "currency_code", "declared_value", "delivery_experience", "from_address_city", "from_address_country_code", "from_address_line", "from_address_mail", "from_address_name", "from_address_phone", "from_address_postal_code", "package_dimension_height", "package_dimension_length", "package_dimension_unit", "package_dimension_width", "shipping_date", "weight", "weight_unit"],
                            "status": [],
                            "optional_args": ["delivery_date", "from_address_state_province", "line"],
                            "args_description": {
                                "line": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "weight": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "weight_unit": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "currency_code": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "delivery_date": {
                                    "type": "date",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "shipping_date": {
                                    "type": "date",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "carrier_pickup": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "declared_value": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_city": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_line": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_mail": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_name": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_phone": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "delivery_experience": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "package_dimension_unit": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "package_dimension_width": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_postal_code": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "package_dimension_height": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "package_dimension_length": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_country_code": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                },
                                "from_address_state_province": {
                                    "type": "string",
                                    "depends_on": null,
                                    "valid_values": {},
                                    "default_value": null,
                                    "accept_free_values": true
                                }
                            }
                        }
                    },
                    "carriers": {
                        "4PX": {
                            "label": "4PX",
                            "lengow_code": null
                        },
                        "A-1": {
                            "label": "A-1",
                            "lengow_code": null
                        },
                        "ABF": {
                            "label": "ABF",
                            "lengow_code": null
                        },
                        "ATS": {
                            "label": "ATS",
                            "lengow_code": null
                        },
                        "BJS": {
                            "label": "BJS",
                            "lengow_code": null
                        },
                        "BRT": {
                            "label": "BRT",
                            "lengow_code": null
                        },
                        "CDC": {
                            "label": "CDC",
                            "lengow_code": null
                        },
                        "DHL": {
                            "label": "DHL",
                            "lengow_code": null
                        },
                        "DPD": {
                            "label": "DPD",
                            "lengow_code": null
                        },
                        "DSV": {
                            "label": "DSV",
                            "lengow_code": null
                        },
                        "EUB": {
                            "label": "EUB",
                            "lengow_code": null
                        },
                        "GLS": {
                            "label": "GLS",
                            "lengow_code": null
                        },
                        "GO!": {
                            "label": "GO!",
                            "lengow_code": null
                        },
                        "MRW": {
                            "label": "MRW",
                            "lengow_code": null
                        },
                        "OSM": {
                            "label": "OSM",
                            "lengow_code": null
                        },
                        "R+L": {
                            "label": "R+L",
                            "lengow_code": null
                        },
                        "SDA": {
                            "label": "SDA",
                            "lengow_code": null
                        },
                        "SFC": {
                            "label": "SFC",
                            "lengow_code": null
                        },
                        "TNT": {
                            "label": "TNT",
                            "lengow_code": null
                        },
                        "UPS": {
                            "label": "UPS",
                            "lengow_code": null
                        },
                        "VIR": {
                            "label": "VIR",
                            "lengow_code": null
                        },
                        "XDP": {
                            "label": "XDP",
                            "lengow_code": null
                        },
                        "YDH": {
                            "label": "YDH",
                            "lengow_code": null
                        },
                        "CEVA": {
                            "label": "CEVA",
                            "lengow_code": null
                        },
                        "DTDC": {
                            "label": "DTDC",
                            "lengow_code": null
                        },
                        "ECMS": {
                            "label": "ECMS",
                            "lengow_code": null
                        },
                        "Gati": {
                            "label": "Gati",
                            "lengow_code": null
                        },
                        "JCEX": {
                            "label": "JCEX",
                            "lengow_code": null
                        },
                        "Otro": {
                            "label": "Otro",
                            "lengow_code": null
                        },
                        "Saia": {
                            "label": "Saia",
                            "lengow_code": null
                        },
                        "Seur": {
                            "label": "Seur",
                            "lengow_code": null
                        },
                        "USPS": {
                            "label": "USPS",
                            "lengow_code": null
                        },
                        "Arkas": {
                            "label": "Arkas",
                            "lengow_code": null
                        },
                        "DHLPL": {
                            "label": "DHLPL",
                            "lengow_code": null
                        },
                        "Estes": {
                            "label": "Estes",
                            "lengow_code": null
                        },
                        "FedEx": {
                            "label": "FedEx",
                            "lengow_code": null
                        },
                        "Nacex": {
                            "label": "Nacex",
                            "lengow_code": null
                        },
                        "Pilot": {
                            "label": "Pilot",
                            "lengow_code": null
                        },
                        "Rieck": {
                            "label": "Rieck",
                            "lengow_code": null
                        },
                        "Seino": {
                            "label": "Seino",
                            "lengow_code": null
                        },
                        "TIPSA": {
                            "label": "TIPSA",
                            "lengow_code": null
                        },
                        "TNTIT": {
                            "label": "TNTIT",
                            "lengow_code": null
                        },
                        "UPSMI": {
                            "label": "UPSMI",
                            "lengow_code": null
                        },
                        "VNLIN": {
                            "label": "VNLIN",
                            "lengow_code": null
                        },
                        "WINIT": {
                            "label": "WINIT",
                            "lengow_code": null
                        },
                        "Yodel": {
                            "label": "Yodel",
                            "lengow_code": null
                        },
                        "ALLJOY": {
                            "label": "ALLJOY",
                            "lengow_code": null
                        },
                        "Aramex": {
                            "label": "Aramex",
                            "lengow_code": null
                        },
                        "Asgard": {
                            "label": "Asgard",
                            "lengow_code": null
                        },
                        "Assett": {
                            "label": "Assett",
                            "lengow_code": null
                        },
                        "Balnak": {
                            "label": "Balnak",
                            "lengow_code": null
                        },
                        "Bombax": {
                            "label": "Bombax",
                            "lengow_code": null
                        },
                        "Conway": {
                            "label": "Conway",
                            "lengow_code": null
                        },
                        "Dotzot": {
                            "label": "Dotzot",
                            "lengow_code": null
                        },
                        "Energo": {
                            "label": "Energo",
                            "lengow_code": null
                        },
                        "InPost": {
                            "label": "InPost",
                            "lengow_code": null
                        },
                        "NITTSU": {
                            "label": "NITTSU",
                            "lengow_code": null
                        },
                        "Nexive": {
                            "label": "Nexive",
                            "lengow_code": null
                        },
                        "OnTrac": {
                            "label": "OnTrac",
                            "lengow_code": null
                        },
                        "Rhenus": {
                            "label": "Rhenus",
                            "lengow_code": null
                        },
                        "Rivigo": {
                            "label": "Rivigo",
                            "lengow_code": null
                        },
                        "SAGAWA": {
                            "label": "SAGAWA",
                            "lengow_code": null
                        },
                        "SENDLE": {
                            "label": "SENDLE",
                            "lengow_code": null
                        },
                        "Sendle": {
                            "label": "Sendle",
                            "lengow_code": null
                        },
                        "Spoton": {
                            "label": "Spoton",
                            "lengow_code": null
                        },
                        "Target": {
                            "label": "Target",
                            "lengow_code": null
                        },
                        "YAMATO": {
                            "label": "YAMATO",
                            "lengow_code": null
                        },
                        "YANWEN": {
                            "label": "YANWEN",
                            "lengow_code": null
                        },
                        "geodis": {
                            "label": "geodis",
                            "lengow_code": null
                        },
                        "AT POST": {
                            "label": "AT POST",
                            "lengow_code": null
                        },
                        "Asendia": {
                            "label": "Asendia",
                            "lengow_code": null
                        },
                        "Correos": {
                            "label": "Correos",
                            "lengow_code": null
                        },
                        "DACHSER": {
                            "label": "DACHSER",
                            "lengow_code": null
                        },
                        "Fastway": {
                            "label": "Fastway",
                            "lengow_code": null
                        },
                        "HS code": {
                            "label": "HS code",
                            "lengow_code": null
                        },
                        "Heppner": {
                            "label": "Heppner",
                            "lengow_code": null
                        },
                        "Milkman": {
                            "label": "Milkman",
                            "lengow_code": null
                        },
                        "Panther": {
                            "label": "Panther",
                            "lengow_code": null
                        },
                        "Post NL": {
                            "label": "Post NL",
                            "lengow_code": null
                        },
                        "Qxpress": {
                            "label": "Qxpress",
                            "lengow_code": null
                        },
                        "Speedex": {
                            "label": "Speedex",
                            "lengow_code": null
                        },
                        "Trackon": {
                            "label": "Trackon",
                            "lengow_code": null
                        },
                        "iParcel": {
                            "label": "iParcel",
                            "lengow_code": null
                        },
                        "Arrow XL": {
                            "label": "Arrow XL",
                            "lengow_code": null
                        },
                        "Best Buy": {
                            "label": "Best Buy",
                            "lengow_code": null
                        },
                        "BlueDart": {
                            "label": "BlueDart",
                            "lengow_code": null
                        },
                        "Correios": {
                            "label": "Correios",
                            "lengow_code": null
                        },
                        "Endopack": {
                            "label": "Endopack",
                            "lengow_code": null
                        },
                        "Envialia": {
                            "label": "Envialia",
                            "lengow_code": null
                        },
                        "Estafeta": {
                            "label": "Estafeta",
                            "lengow_code": null
                        },
                        "FEDEX_JP": {
                            "label": "FEDEX_JP",
                            "lengow_code": null
                        },
                        "Kargokar": {
                            "label": "Kargokar",
                            "lengow_code": null
                        },
                        "La Poste": {
                            "label": "La Poste",
                            "lengow_code": null
                        },
                        "Landmark": {
                            "label": "Landmark",
                            "lengow_code": null
                        },
                        "Ninjavan": {
                            "label": "Ninjavan",
                            "lengow_code": null
                        },
                        "PostNord": {
                            "label": "PostNord",
                            "lengow_code": null
                        },
                        "QExpress": {
                            "label": "QExpress",
                            "lengow_code": null
                        },
                        "Tourline": {
                            "label": "Tourline",
                            "lengow_code": null
                        },
                        "Whizzard": {
                            "label": "Whizzard",
                            "lengow_code": null
                        },
                        "AFL\/Fedex": {
                            "label": "AFL\/Fedex",
                            "lengow_code": null
                        },
                        "CELERITAS": {
                            "label": "CELERITAS",
                            "lengow_code": null
                        },
                        "Cititrans": {
                            "label": "Cititrans",
                            "lengow_code": null
                        },
                        "City Link": {
                            "label": "City Link",
                            "lengow_code": null
                        },
                        "Coliposte": {
                            "label": "Coliposte",
                            "lengow_code": null
                        },
                        "Colissimo": {
                            "label": "Colissimo",
                            "lengow_code": null
                        },
                        "DHL Kargo": {
                            "label": "DHL Kargo",
                            "lengow_code": null
                        },
                        "DHL-Paket": {
                            "label": "DHL-Paket",
                            "lengow_code": null
                        },
                        "Delhivery": {
                            "label": "Delhivery",
                            "lengow_code": null
                        },
                        "DirectLog": {
                            "label": "DirectLog",
                            "lengow_code": null
                        },
                        "Lasership": {
                            "label": "Lasership",
                            "lengow_code": null
                        },
                        "MNG Kargo": {
                            "label": "MNG Kargo",
                            "lengow_code": null
                        },
                        "PTT Kargo": {
                            "label": "PTT Kargo",
                            "lengow_code": null
                        },
                        "PUROLATOR": {
                            "label": "PUROLATOR",
                            "lengow_code": null
                        },
                        "Parcelnet": {
                            "label": "Parcelnet",
                            "lengow_code": null
                        },
                        "Smartmail": {
                            "label": "Smartmail",
                            "lengow_code": null
                        },
                        "TNT Kargo": {
                            "label": "TNT Kargo",
                            "lengow_code": null
                        },
                        "Tuffnells": {
                            "label": "Tuffnells",
                            "lengow_code": null
                        },
                        "AAA Cooper": {
                            "label": "AAA Cooper",
                            "lengow_code": null
                        },
                        "Aras Kargo": {
                            "label": "Aras Kargo",
                            "lengow_code": null
                        },
                        "Bo\u011fazi\u00e7i": {
                            "label": "Bo\u011fazi\u00e7i",
                            "lengow_code": null
                        },
                        "CTTExpress": {
                            "label": "CTTExpress",
                            "lengow_code": null
                        },
                        "Cart2India": {
                            "label": "Cart2India",
                            "lengow_code": null
                        },
                        "China Post": {
                            "label": "China Post",
                            "lengow_code": null
                        },
                        "Chronopost": {
                            "label": "Chronopost",
                            "lengow_code": null
                        },
                        "DX Freight": {
                            "label": "DX Freight",
                            "lengow_code": null
                        },
                        "First Mile": {
                            "label": "First Mile",
                            "lengow_code": null
                        },
                        "India Post": {
                            "label": "India Post",
                            "lengow_code": null
                        },
                        "JP_EXPRESS": {
                            "label": "JP_EXPRESS",
                            "lengow_code": null
                        },
                        "Japan Post": {
                            "label": "Japan Post",
                            "lengow_code": null
                        },
                        "Newgistics": {
                            "label": "Newgistics",
                            "lengow_code": null
                        },
                        "Roadrunner": {
                            "label": "Roadrunner",
                            "lengow_code": null
                        },
                        "Royal Mail": {
                            "label": "Royal Mail",
                            "lengow_code": null
                        },
                        "SF Express": {
                            "label": "SF Express",
                            "lengow_code": null
                        },
                        "Safexpress": {
                            "label": "Safexpress",
                            "lengow_code": null
                        },
                        "ShipGlobal": {
                            "label": "ShipGlobal",
                            "lengow_code": null
                        },
                        "Spring GDS": {
                            "label": "Spring GDS",
                            "lengow_code": null
                        },
                        "Streamlite": {
                            "label": "Streamlite",
                            "lengow_code": null
                        },
                        "TransFolha": {
                            "label": "TransFolha",
                            "lengow_code": null
                        },
                        "Xpressbees": {
                            "label": "Xpressbees",
                            "lengow_code": null
                        },
                        "AUSSIE_POST": {
                            "label": "AUSSIE_POST",
                            "lengow_code": null
                        },
                        "COLIS PRIVE": {
                            "label": "COLIS PRIVE",
                            "lengow_code": null
                        },
                        "Canada Post": {
                            "label": "Canada Post",
                            "lengow_code": null
                        },
                        "Colis Prive": {
                            "label": "Colis Prive",
                            "lengow_code": null
                        },
                        "DB Schenker": {
                            "label": "DB Schenker",
                            "lengow_code": null
                        },
                        "DHL Express": {
                            "label": "DHL Express",
                            "lengow_code": null
                        },
                        "DHL Freight": {
                            "label": "DHL Freight",
                            "lengow_code": null
                        },
                        "Fillo Kargo": {
                            "label": "Fillo Kargo",
                            "lengow_code": null
                        },
                        "GEL Express": {
                            "label": "GEL Express",
                            "lengow_code": null
                        },
                        "Metro Kargo": {
                            "label": "Metro Kargo",
                            "lengow_code": null
                        },
                        "Parcelforce": {
                            "label": "Parcelforce",
                            "lengow_code": null
                        },
                        "Polish Post": {
                            "label": "Polish Post",
                            "lengow_code": null
                        },
                        "Raben Group": {
                            "label": "Raben Group",
                            "lengow_code": null
                        },
                        "STO Express": {
                            "label": "STO Express",
                            "lengow_code": null
                        },
                        "Selem Kargo": {
                            "label": "Selem Kargo",
                            "lengow_code": null
                        },
                        "ShipEconomy": {
                            "label": "ShipEconomy",
                            "lengow_code": null
                        },
                        "UPS Freight": {
                            "label": "UPS Freight",
                            "lengow_code": null
                        },
                        "WanbExpress": {
                            "label": "WanbExpress",
                            "lengow_code": null
                        },
                        "XPO Freight": {
                            "label": "XPO Freight",
                            "lengow_code": null
                        },
                        "YTO Express": {
                            "label": "YTO Express",
                            "lengow_code": null
                        },
                        "Yun Express": {
                            "label": "Yun Express",
                            "lengow_code": null
                        },
                        "ZTO Express": {
                            "label": "ZTO Express",
                            "lengow_code": null
                        },
                        "Best Express": {
                            "label": "Best Express",
                            "lengow_code": null
                        },
                        "Blue Package": {
                            "label": "Blue Package",
                            "lengow_code": null
                        },
                        "Ecom Express": {
                            "label": "Ecom Express",
                            "lengow_code": null
                        },
                        "First Flight": {
                            "label": "First Flight",
                            "lengow_code": null
                        },
                        "IDS Netzwerk": {
                            "label": "IDS Netzwerk",
                            "lengow_code": null
                        },
                        "Kuehne+Nagel": {
                            "label": "Kuehne+Nagel",
                            "lengow_code": null
                        },
                        "Old Dominion": {
                            "label": "Old Dominion",
                            "lengow_code": null
                        },
                        "Professional": {
                            "label": "Professional",
                            "lengow_code": null
                        },
                        "Ship Delight": {
                            "label": "Ship Delight",
                            "lengow_code": null
                        },
                        "S\u00fcrat Kargo": {
                            "label": "S\u00fcrat Kargo",
                            "lengow_code": null
                        },
                        "Ceva Lojistik": {
                            "label": "Ceva Lojistik",
                            "lengow_code": null
                        },
                        "DHL eCommerce": {
                            "label": "DHL eCommerce",
                            "lengow_code": null
                        },
                        "Deutsche Post": {
                            "label": "Deutsche Post",
                            "lengow_code": null
                        },
                        "Emirates Post": {
                            "label": "Emirates Post",
                            "lengow_code": null
                        },
                        "Fedex Freight": {
                            "label": "Fedex Freight",
                            "lengow_code": null
                        },
                        "Geopost Kargo": {
                            "label": "Geopost Kargo",
                            "lengow_code": null
                        },
                        "Hongkong Post": {
                            "label": "Hongkong Post",
                            "lengow_code": null
                        },
                        "ICC Worldwide": {
                            "label": "ICC Worldwide",
                            "lengow_code": null
                        },
                        "Narpost Kargo": {
                            "label": "Narpost Kargo",
                            "lengow_code": null
                        },
                        "NipponExpress": {
                            "label": "NipponExpress",
                            "lengow_code": null
                        },
                        "Pilot Freight": {
                            "label": "Pilot Freight",
                            "lengow_code": null
                        },
                        "SagawaExpress": {
                            "label": "SagawaExpress",
                            "lengow_code": null
                        },
                        "Self Delivery": {
                            "label": "Self Delivery",
                            "lengow_code": null
                        },
                        "Total Express": {
                            "label": "Total Express",
                            "lengow_code": null
                        },
                        "Urban Express": {
                            "label": "Urban Express",
                            "lengow_code": null
                        },
                        "Yunda Express": {
                            "label": "Yunda Express",
                            "lengow_code": null
                        },
                        "Australia Post": {
                            "label": "Australia Post",
                            "lengow_code": null
                        },
                        "Chrono Express": {
                            "label": "Chrono Express",
                            "lengow_code": null
                        },
                        "CouriersPlease": {
                            "label": "CouriersPlease",
                            "lengow_code": null
                        },
                        "Home Logistics": {
                            "label": "Home Logistics",
                            "lengow_code": null
                        },
                        "Horoz Lojistik": {
                            "label": "Horoz Lojistik",
                            "lengow_code": null
                        },
                        "Poste Italiane": {
                            "label": "Poste Italiane",
                            "lengow_code": null
                        },
                        "Ship Global US": {
                            "label": "Ship Global US",
                            "lengow_code": null
                        },
                        "Singapore Post": {
                            "label": "Singapore Post",
                            "lengow_code": null
                        },
                        "Tezel Lojistik": {
                            "label": "Tezel Lojistik",
                            "lengow_code": null
                        },
                        "Yellow Freight": {
                            "label": "Yellow Freight",
                            "lengow_code": null
                        },
                        "Yurti\u00e7i Kargo": {
                            "label": "Yurti\u00e7i Kargo",
                            "lengow_code": null
                        },
                        "Amazon Shipping": {
                            "label": "Amazon Shipping",
                            "lengow_code": null
                        },
                        "Correos Express": {
                            "label": "Correos Express",
                            "lengow_code": null
                        },
                        "Couriers Please": {
                            "label": "Couriers Please",
                            "lengow_code": null
                        },
                        "DHL Global Mail": {
                            "label": "DHL Global Mail",
                            "lengow_code": null
                        },
                        "FedEx SmartPost": {
                            "label": "FedEx SmartPost",
                            "lengow_code": null
                        },
                        "OneWorldExpress": {
                            "label": "OneWorldExpress",
                            "lengow_code": null
                        },
                        "YamatoTransport": {
                            "label": "YamatoTransport",
                            "lengow_code": null
                        },
                        "Digital Delivery": {
                            "label": "Digital Delivery",
                            "lengow_code": null
                        },
                        "Geodis Calberson": {
                            "label": "Geodis Calberson",
                            "lengow_code": null
                        },
                        "Hunter Logistics": {
                            "label": "Hunter Logistics",
                            "lengow_code": null
                        },
                        "Overnite Express": {
                            "label": "Overnite Express",
                            "lengow_code": null
                        },
                        "Shunfeng Express": {
                            "label": "Shunfeng Express",
                            "lengow_code": null
                        },
                        "DHL Home Delivery": {
                            "label": "DHL Home Delivery",
                            "lengow_code": null
                        },
                        "First Flight China": {
                            "label": "First Flight China",
                            "lengow_code": null
                        },
                        "StarTrack-ArticleID": {
                            "label": "StarTrack-ArticleID",
                            "lengow_code": null
                        },
                        "Toll Global Express": {
                            "label": "Toll Global Express",
                            "lengow_code": null
                        },
                        "Watkins and Shepard": {
                            "label": "Watkins and Shepard",
                            "lengow_code": null
                        },
                        "SEINO TRANSPORTATION": {
                            "label": "SEINO TRANSPORTATION",
                            "lengow_code": null
                        },
                        "Shree Maruti Courier": {
                            "label": "Shree Maruti Courier",
                            "lengow_code": null
                        },
                        "UPS Mail Innovations": {
                            "label": "UPS Mail Innovations",
                            "lengow_code": null
                        },
                        "StarTrack-Consignment": {
                            "label": "StarTrack-Consignment",
                            "lengow_code": null
                        },
                        "Hermes Logistik Gruppe": {
                            "label": "Hermes Logistik Gruppe",
                            "lengow_code": null
                        },
                        "Shree Tirupati Courier": {
                            "label": "Shree Tirupati Courier",
                            "lengow_code": null
                        },
                        "AustraliaPost-ArticleId": {
                            "label": "AustraliaPost-ArticleId",
                            "lengow_code": null
                        },
                        "Beijing Quanfeng Express": {
                            "label": "Beijing Quanfeng Express",
                            "lengow_code": null
                        },
                        "AustraliaPost-Consignment": {
                            "label": "AustraliaPost-Consignment",
                            "lengow_code": null
                        },
                        "The Professional Couriers": {
                            "label": "The Professional Couriers",
                            "lengow_code": null
                        },
                        "Hermes Einrichtungsservice": {
                            "label": "Hermes Einrichtungsservice",
                            "lengow_code": null
                        },
                        "South Eastern Freight Lines": {
                            "label": "South Eastern Freight Lines",
                            "lengow_code": null
                        }
                    },
                    "shipping_methods": {
                        "NextDay": {
                            "label": "NextDay",
                            "lengow_code": "nextday"
                        },
                        "SameDay": {
                            "label": "SameDay",
                            "lengow_code": "sameday"
                        },
                        "Priority": {
                            "label": "Priority",
                            "lengow_code": null
                        },
                        "Standard": {
                            "label": "Standard",
                            "lengow_code": "standard"
                        },
                        "Expedited": {
                            "label": "Expedited",
                            "lengow_code": "expedited"
                        },
                        "Scheduled": {
                            "label": "Scheduled",
                            "lengow_code": "scheduled"
                        },
                        "SecondDay": {
                            "label": "SecondDay",
                            "lengow_code": "secondday"
                        },
                        "FreeEconomy": {
                            "label": "FreeEconomy",
                            "lengow_code": "freeeconomy"
                        },
                        "BuyerTaxInfo": {
                            "label": "BuyerTaxInfo",
                            "lengow_code": "buyertaxinfo"
                        }
                    }
                },
                "country": "FRA",
                "package": "sp_amazon.mp.fr",
                "homepage": "http:\/\/www.amazon.com\/",
                "description": "A generalist marketplace, Amazon is designed for all sellers seeking to reach a wide audience whether locally or internationally. Appear in Amazon\'s search results thanks to a well-structured feed, create detailed and attractive pages and win the buy box. \r\nAmazon offers you a broad range of associated services such as \u201cFulfilled by Amazon\u201d, which allows you to delegate the management of your stock and the dispatching of your products to the ecommerce giant. \r\n\r\nChoose your feed:\r\n- The Amazon feed to create and publish your product catalogue for products that are not yet sold on the marketplace.\r\n- The Listing Loader feed to attach your products to existing product sheets.\r\n\r\nStart selling on the world\u2019s leading ecommerce site!",
                "legacy_code": "amazon",
                "country_iso_a2": "FR"
            }
        }';
    }
}

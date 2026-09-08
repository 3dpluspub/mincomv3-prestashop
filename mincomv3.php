<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/Mincomv3Model.php';

class MinComV3 extends Module
{
    public function __construct()
    {
        $this->name = 'mincomv3';
        $this->tab = 'shipping_logistics';
        $this->version = '3.2.0';
        $this->author = '3dplus web agency';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => '9.99.99',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('MinComV3 - Frais et Minimum par Ville');
        $this->description = $this->l('Gestion des frais de port et minimum de commande par ville et code postal.');
    }

    public function install(): bool
    {
        $sqlFile = dirname(__FILE__) . '/sql/install.sql';
        if (file_exists($sqlFile)) {
            $sqlContent = file_get_contents($sqlFile);
            if ($sqlContent !== false) {
                $sqlContent = str_replace('PREFIX_', _DB_PREFIX_, $sqlContent);
                $sqlQueries = preg_split("/;\s*[\r\n]+/", $sqlContent);
                if (is_array($sqlQueries)) {
                    foreach ($sqlQueries as $query) {
                        $trimmedQuery = trim((string)$query);
                        if (!empty($trimmedQuery)) {
                            try {
                                Db::getInstance()->execute($trimmedQuery);
                            } catch (Exception $e) {
                                PrestaShopLogger::addLog('MinComV3 SQL Error: ' . $e->getMessage(), 3);
                            }
                        }
                    }
                }
            }
        }

        return parent::install() &&
            $this->registerHook('actionDispatcher') &&
            $this->registerHook('getOrderShippingCost') &&
            $this->registerHook('getOrderShippingCostExternal') &&
            $this->registerHook('actionCarrierProcess') &&
            $this->registerHook('actionValidateOrder') &&
            $this->installCarrier() &&
            $this->installTab();
    }

    public function uninstall(): bool
    {
        $sqlFile = dirname(__FILE__) . '/sql/uninstall.sql';
        if (file_exists($sqlFile)) {
            $sqlContent = file_get_contents($sqlFile);
            if ($sqlContent !== false) {
                $sqlContent = str_replace('PREFIX_', _DB_PREFIX_, $sqlContent);
                $sqlQueries = preg_split("/;\s*[\r\n]+/", $sqlContent);
                if (is_array($sqlQueries)) {
                    foreach ($sqlQueries as $query) {
                        $trimmedQuery = trim((string)$query);
                        if (!empty($trimmedQuery)) {
                            try {
                                Db::getInstance()->execute($trimmedQuery);
                            } catch (Exception $e) {
                                PrestaShopLogger::addLog('MinComV3 SQL Error: ' . $e->getMessage(), 3);
                            }
                        }
                    }
                }
            }
        }

        $this->uninstallCarrier();
        return parent::uninstall() && $this->uninstallTab();
    }

    protected function installCarrier(): bool
    {
        try {
            $carrier = new Carrier();
            $carrier->name = 'Livraison MinComV3';
            $carrier->is_module = true;
            $carrier->shipping_external = true;
            $carrier->active = true;
            $carrier->deleted = 0;
            $carrier->shipping_handling = false;
            $carrier->range_behavior = 0;
            $carrier->is_free = false;
            $carrier->shipping_method = Carrier::SHIPPING_METHOD_PRICE;

            $languages = Language::getLanguages(true);
            if (is_array($languages)) {
                foreach ($languages as $lang) {
                    $idLang = (int)$lang['id_lang'];
                    $carrier->name[$idLang] = 'Livraison Locale';
                    $carrier->delay[$idLang] = 'Livraison rapide à domicile';
                }
            }

            if ($carrier->add()) {
                $zones = Zone::getZones(false);
                if (is_array($zones)) {
                    foreach ($zones as $zone) {
                        $carrier->addZone((int)$zone['id_zone']);
                    }
                }
                Configuration::updateValue('MINCOMV3_CARRIER_ID', (int)$carrier->id);
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('MinComV3 Carrier Install Error: ' . $e->getMessage(), 3);
            return false;
        }
        return true;
    }

    protected function uninstallCarrier(): bool
    {
        try {
            $id_carrier = (int)Configuration::get('MINCOMV3_CARRIER_ID');
            if ($id_carrier) {
                $carrier = new Carrier($id_carrier);
                if (Validate::isLoadedObject($carrier)) {
                    $carrier->deleted = 1;
                    $carrier->update();
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('MinComV3 Carrier Uninstall Error: ' . $e->getMessage(), 3);
        }
        return true;
    }

    protected function installTab(): bool
    {
        try {
            $id_tab = (int)Tab::getIdFromClassName('AdminMincomv3');
            if (!$id_tab) {
                $tab = new Tab();
                $tab->class_name = 'AdminMincomv3';
                $tab->module = $this->name;
                $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentShipping');
                $tab->icon = 'local_shipping';
                $tab->name[(int)Configuration::get('PS_LANG_DEFAULT')] = 'Gestion Villes / MinComV3';

                return (bool)$tab->add();
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('MinComV3 Tab Install Error: ' . $e->getMessage(), 3);
        }
        return true;
    }

    protected function uninstallTab(): bool
    {
        try {
            $id_tab = (int)Tab::getIdFromClassName('AdminMincomv3');
            if ($id_tab) {
                $tab = new Tab($id_tab);
                return (bool)$tab->delete();
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('MinComV3 Tab Uninstall Error: ' . $e->getMessage(), 3);
        }
        return true;
    }

    public function getContent(): string
    {
        try {
            $url = $this->context->link->getAdminLink('AdminMincomv3');
            Tools::redirectAdmin($url);
        } catch (Exception $e) {
            PrestaShopLogger::addLog('MinComV3 getContent Error: ' . $e->getMessage(), 3);
        }
        return '';
    }

    protected function getAddressObject($idAddress): Address|bool
    {
        if (empty($idAddress)) {
            return false;
        }

        try {
            $address = new Address((int)$idAddress);
            
            if (!Validate::isLoadedObject($address)) {
                return false;
            }

            return $address;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('MinComV3 Address Error: ' . $e->getMessage(), 3);
            return false;
        }
    }

    protected function getCityDataFromAddress($address): array|bool
    {
        if (!is_object($address) || !Validate::isLoadedObject($address)) {
            return false;
        }

        try {
            // 1. Posta koduna göre kesin eşleşme (Örn: 1227)
            if (!empty($address->postcode)) {
                $db = Db::getInstance();
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'mincomv3` WHERE TRIM(`postal_code`) = %s AND `active` = 1 LIMIT 1';
                $results = $db->executeS($sql, [pSQL(trim($address->postcode))]);
                
                if (is_array($results) && count($results) > 0) {
                    return $results[0];
                }
            }

            // 2. Şehir adına göre esnek eşleşme
            if (!empty($address->city)) {
                $db = Db::getInstance();
                $escapedCity = str_replace(['%', '_'], ['\\%', '\\_'], pSQL(trim($address->city)));
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'mincomv3` WHERE LOWER(`city_name`) LIKE LOWER("%' . $escapedCity . '%") ESCAPE "\\" AND `active` = 1 LIMIT 1';
                $row = $db->getRow($sql);
                
                if ($row) {
                    return $row;
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('MinComV3 getCityDataFromAddress Error: ' . $e->getMessage(), 3);
        }

        return false;
    }

    public function hookActionDispatcher($params): void
    {
        try {
            $controller = Dispatcher::getInstance()->getController();
            if (in_array($controller, ['order', 'checkout'])) {
                $cart = $this->context->cart;
                if (is_object($cart) && !empty($cart->id_address_delivery)) {
                    $address = $this->getAddressObject($cart->id_address_delivery);
                    if (!$address) {
                        return;
                    }

                    $cityData = $this->getCityDataFromAddress($address);
                    if ($cityData) {
                        $cartTotal = (float)$cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
                        $minOrderAmount = (float)$cityData['free_shipping_amount'];

                        if ($minOrderAmount > 0 && $cartTotal < $minOrderAmount) {
                            $this->context->controller->errors[] = sprintf(
                                'Montant insuffisant ! Le minimum requis pour cette zone est de %.2f CHF. (Panier actuel : %.2f CHF)',
                                $minOrderAmount,
                                $cartTotal
                            );
                            if ($controller !== 'cart') {
                                Tools::redirect('index.php?controller=cart');
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('MinComV3 hookActionDispatcher Error: ' . $e->getMessage(), 3);
        }
    }

    public function hookGetOrderShippingCost($params)
    {
        return $this->calculateShippingCost($params);
    }

    public function hookGetOrderShippingCostExternal($params)
    {
        return $this->calculateShippingCost($params);
    }

    protected function calculateShippingCost($params): float|bool
    {
        try {
            $cart = $this->context->cart;
            if (!is_object($cart) || empty($cart->id_address_delivery)) {
                return false;
            }

            $address = $this->getAddressObject($cart->id_address_delivery);
            if (!$address) {
                return false;
            }

            $cityData = $this->getCityDataFromAddress($address);
            if (!$cityData) {
                return false;
            }

            $cartTotal = (float)$cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
            $freeShippingAmount = (float)$cityData['free_shipping_amount'];
            $baseShippingCost = (float)$cityData['shipping_cost'];

            // Tutar Franco (minimum sipariş / ücretsiz kargo) sınırının üzerindeyse kargo ücretsiz
            if ($freeShippingAmount > 0 && $cartTotal >= $freeShippingAmount) {
                return 0.00;
            }

            // Tutar yetersizse baz kargo ücretini (örn: 5 CHF) sepete ekle
            return $baseShippingCost;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('MinComV3 calculateShippingCost Error: ' . $e->getMessage(), 3);
            return false;
        }
    }

    public function hookActionCarrierProcess($params): void
    {
        try {
            $cart = $this->context->cart;
            if (!is_object($cart) || empty($cart->id_address_delivery)) {
                return;
            }

            $address = $this->getAddressObject($cart->id_address_delivery);
            if (!$address) {
                return;
            }

            $cityData = $this->getCityDataFromAddress($address);
            if (!$cityData) {
                return;
            }

            $cartTotal = (float)$cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
            $minOrderAmount = (float)$cityData['free_shipping_amount'];

            if ($minOrderAmount > 0 && $cartTotal < $minOrderAmount) {
                $this->context->controller->errors[] = sprintf(
                    'Montant insuffisant ! Le minimum requis pour cette zone est de %.2f CHF. (Panier actuel : %.2f CHF)',
                    $minOrderAmount,
                    $cartTotal
                );
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('MinComV3 hookActionCarrierProcess Error: ' . $e->getMessage(), 3);
        }
    }

    public function hookActionValidateOrder($params): void
    {
        try {
            $cart = isset($params['cart']) ? $params['cart'] : $this->context->cart;
            if (!is_object($cart) || empty($cart->id_address_delivery)) {
                return;
            }

            $address = $this->getAddressObject($cart->id_address_delivery);
            if (!$address) {
                return;
            }

            $cityData = $this->getCityDataFromAddress($address);
            if (!$cityData) {
                return;
            }

            $cartTotal = (float)$cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
            $minOrderAmount = (float)$cityData['free_shipping_amount'];

            if ($minOrderAmount > 0 && $cartTotal < $minOrderAmount) {
                throw new PrestaShopException(sprintf(
                    'COMMANDE REFUSÉE : Le minimum requis pour cette zone est de %.2f CHF (Panier: %.2f CHF).',
                    $minOrderAmount,
                    $cartTotal
                ));
            }
        } catch (PrestaShopException $e) {
            throw $e;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('MinComV3 hookActionValidateOrder Error: ' . $e->getMessage(), 3);
        }
    }
}

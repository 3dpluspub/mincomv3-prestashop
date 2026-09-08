<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Mincomv3Model extends ObjectModel
{
    public int $id_mincomv3;
    public string $postal_code = '';
    public string $city_name = '';
    public float $shipping_cost = 0.00;
    public float $free_shipping_amount = 0.00;
    public int $active = 1;
    public string $date_add = '';
    public string $date_upd = '';

    public static $definition = [
        'table' => 'mincomv3',
        'primary' => 'id_mincomv3',
        'fields' => [
            'postal_code' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 10],
            'city_name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 100],
            'shipping_cost' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true],
            'free_shipping_amount' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
        ],
    ];

    public function __construct(?int $id = null, ?int $idLang = null, ?int $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
    }

    public static function getAllCities(): array
    {
        try {
            $db = Db::getInstance();
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'mincomv3` ORDER BY `city_name` ASC';
            $results = $db->executeS($sql);
            return is_array($results) ? $results : [];
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Mincomv3Model getAllCities Error: ' . $e->getMessage(), 3);
            return [];
        }
    }

    public static function getCityById(int $id): array|bool
    {
        try {
            $db = Db::getInstance();
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'mincomv3` WHERE `id_mincomv3` = %s';
            return $db->getRow($sql, [$id]);
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Mincomv3Model getCityById Error: ' . $e->getMessage(), 3);
            return false;
        }
    }

    public static function getCityByPostalCode(string $postalCode): array|bool
    {
        try {
            $db = Db::getInstance();
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'mincomv3` WHERE TRIM(`postal_code`) = %s AND `active` = 1';
            return $db->getRow($sql, [pSQL(trim($postalCode))]);
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Mincomv3Model getCityByPostalCode Error: ' . $e->getMessage(), 3);
            return false;
        }
    }

    public static function getCityByName(string $cityName): array|bool
    {
        try {
            $db = Db::getInstance();
            $escapedCity = str_replace(['%', '_'], ['\\%', '\\_'], pSQL(trim($cityName)));
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'mincomv3` WHERE LOWER(`city_name`) LIKE LOWER("%' . $escapedCity . '%") AND `active` = 1 LIMIT 1';
            return $db->getRow($sql);
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Mincomv3Model getCityByName Error: ' . $e->getMessage(), 3);
            return false;
        }
    }

    public function add(bool $autoDate = true, bool $nullValues = false): bool
    {
        try {
            if (!$this->postal_code && !$this->city_name) {
                throw new Exception('Posta kodu veya şehir adı gereklidir.');
            }

            if ($this->shipping_cost < 0 || $this->free_shipping_amount < 0) {
                throw new Exception('Gümrük ücretleri negatif olamaz.');
            }

            return parent::add($autoDate, $nullValues);
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Mincomv3Model add Error: ' . $e->getMessage(), 3);
            return false;
        }
    }

    public function update(bool $nullValues = false): bool
    {
        try {
            if (!$this->postal_code && !$this->city_name) {
                throw new Exception('Posta kodu veya şehir adı gereklidir.');
            }

            if ($this->shipping_cost < 0 || $this->free_shipping_amount < 0) {
                throw new Exception('Gümrük ücretleri negatif olamaz.');
            }

            return parent::update($nullValues);
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Mincomv3Model update Error: ' . $e->getMessage(), 3);
            return false;
        }
    }

    public function delete(): bool
    {
        try {
            return parent::delete();
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Mincomv3Model delete Error: ' . $e->getMessage(), 3);
            return false;
        }
    }
}

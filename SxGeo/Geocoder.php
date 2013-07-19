<?php
/***************************************************************************\
| Sypex Geo                  version 2.1.0                                  |
| (c)2006-2012 zapimir       zapimir@zapimir.net       http://sypex.net/    |
| (c)2006-2012 BINOVATOR     info@sypex.net                                 |
|---------------------------------------------------------------------------|
|     created: 2006.10.17 18:33              modified: 2012.06.26 19:55     |
|---------------------------------------------------------------------------|
| Sypex Geo is released under the terms of the BSD license                  |
|   http://sypex.net/bsd_license.txt                                        |
\***************************************************************************/

namespace SxGeo;

/**
 *
 * @author zapimir zapimir@zapimir.net
 * @author Konstantin.Myakshkin <koc-dp@yandex.ru>
 */
class Geocoder
{
    const VERSION = '3.0-DEV';

    const MODE_FILE = 0;
    const MODE_MEMORY = 1;
    const MODE_BATCH = 2;

    protected $fh;
    protected $info;
    protected $range;
    protected $dbBegin;
    protected $bIdxStr;
    protected $mIdxStr;
    protected $bIdxArr;
    protected $mIdxArr;
    protected $bIdxLen;
    protected $mIdxLen;
    protected $dbItems;
    protected $db;
    protected $regionsDb;
    protected $citiesDb;
    protected $idLength;
    protected $blockLength;
    protected $maxRegion;
    protected $maxCity;

    protected $batchMode = false;
    protected $memoryMode = false;

    protected static $cc2iso = array(
		'', 'AP', 'EU', 'AD', 'AE', 'AF', 'AG', 'AI', 'AL', 'AM', 'AN', 'AO', 'AQ',
		'AR', 'AS', 'AT', 'AU', 'AW', 'AZ', 'BA', 'BB', 'BD', 'BE', 'BF', 'BG', 'BH',
		'BI', 'BJ', 'BM', 'BN', 'BO', 'BR', 'BS', 'BT', 'BV', 'BW', 'BY', 'BZ', 'CA',
		'CC', 'CD', 'CF', 'CG', 'CH', 'CI', 'CK', 'CL', 'CM', 'CN', 'CO', 'CR', 'CU',
		'CV', 'CX', 'CY', 'CZ', 'DE', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EC', 'EE', 'EG',
		'EH', 'ER', 'ES', 'ET', 'FI', 'FJ', 'FK', 'FM', 'FO', 'FR', 'FX', 'GA', 'GB',
		'GD', 'GE', 'GF', 'GH', 'GI', 'GL', 'GM', 'GN', 'GP', 'GQ', 'GR', 'GS', 'GT',
		'GU', 'GW', 'GY', 'HK', 'HM', 'HN', 'HR', 'HT', 'HU', 'ID', 'IE', 'IL', 'IN',
		'IO', 'IQ', 'IR', 'IS', 'IT', 'JM', 'JO', 'JP', 'KE', 'KG', 'KH', 'KI', 'KM',
		'KN', 'KP', 'KR', 'KW', 'KY', 'KZ', 'LA', 'LB', 'LC', 'LI', 'LK', 'LR', 'LS',
		'LT', 'LU', 'LV', 'LY', 'MA', 'MC', 'MD', 'MG', 'MH', 'MK', 'ML', 'MM', 'MN',
		'MO', 'MP', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW', 'MX', 'MY', 'MZ', 'NA',
		'NC', 'NE', 'NF', 'NG', 'NI', 'NL', 'NO', 'NP', 'NR', 'NU', 'NZ', 'OM', 'PA',
		'PE', 'PF', 'PG', 'PH', 'PK', 'PL', 'PM', 'PN', 'PR', 'PS', 'PT', 'PW', 'PY',
		'QA', 'RE', 'RO', 'RU', 'RW', 'SA', 'SB', 'SC', 'SD', 'SE', 'SG', 'SH', 'SI',
		'SJ', 'SK', 'SL', 'SM', 'SN', 'SO', 'SR', 'ST', 'SV', 'SY', 'SZ', 'TC', 'TD',
		'TF', 'TG', 'TH', 'TJ', 'TK', 'TM', 'TN', 'TO', 'TL', 'TR', 'TT', 'TV', 'TW',
		'TZ', 'UA', 'UG', 'UM', 'US', 'UY', 'UZ', 'VA', 'VC', 'VE', 'VG', 'VI', 'VN',
		'VU', 'WF', 'WS', 'YE', 'YT', 'RS', 'ZA', 'ZM', 'ME', 'ZW', 'A1', 'A2', 'O1',
		'AX', 'GG', 'IM', 'JE', 'BL', 'MF'
	);

    protected static $tz = array(
        '',  'Africa/Abidjan', 'Africa/Accra', 'Africa/Addis_Ababa', 'Africa/Algiers', 'Africa/Bamako', 'Africa/Banjul',
        'Africa/Blantyre', 'Africa/Brazzaville', 'Africa/Bujumbura', 'Africa/Cairo', 'Africa/Casablanca', 'Africa/Ceuta',
        'Africa/Conakry', 'Africa/Dakar', 'Africa/Dar_es_Salaam', 'Africa/Douala', 'Africa/Freetown', 'Africa/Gaborone',
        'Africa/Harare', 'Africa/Johannesburg', 'Africa/Kampala', 'Africa/Khartoum', 'Africa/Kigali', 'Africa/Kinshasa',
        'Africa/Lagos', 'Africa/Libreville', 'Africa/Luanda', 'Africa/Lubumbashi', 'Africa/Lusaka', 'Africa/Malabo',
        'Africa/Maputo', 'Africa/Maseru', 'Africa/Mbabane', 'Africa/Mogadishu', 'Africa/Monrovia', 'Africa/Nairobi',
        'Africa/Ndjamena', 'Africa/Niamey', 'Africa/Nouakchott', 'Africa/Ouagadougou', 'Africa/Porto-Novo', 'Africa/Tripoli',
        'Africa/Tunis', 'Africa/Windhoek', 'America/Anchorage', 'America/Anguilla', 'America/Antigua', 'America/Araguaina',
        'America/Argentina/Buenos_Aires', 'America/Argentina/Catamarca', 'America/Argentina/Cordoba', 'America/Argentina/Jujuy',
        'America/Argentina/La_Rioja', 'America/Argentina/Mendoza', 'America/Argentina/Rio_Gallegos', 'America/Argentina/Salta',
        'America/Argentina/San_Juan', 'America/Argentina/San_Luis', 'America/Argentina/Tucuman', 'America/Argentina/Ushuaia',
        'America/Asuncion', 'America/Bahia', 'America/Bahia_Banderas', 'America/Barbados', 'America/Belem', 'America/Belize',
        'America/Boa_Vista', 'America/Bogota', 'America/Campo_Grande', 'America/Cancun', 'America/Caracas', 'America/Chicago',
        'America/Chihuahua', 'America/Costa_Rica', 'America/Cuiaba', 'America/Denver', 'America/Dominica', 'America/Edmonton',
        'America/El_Salvador', 'America/Fortaleza', 'America/Godthab', 'America/Grenada', 'America/Guatemala', 'America/Guayaquil',
        'America/Guyana', 'America/Halifax', 'America/Havana', 'America/Hermosillo', 'America/Indianapolis', 'America/Iqaluit',
        'America/Jamaica', 'America/La_Paz', 'America/Lima', 'America/Los_Angeles', 'America/Maceio', 'America/Managua',
        'America/Manaus', 'America/Matamoros', 'America/Mazatlan', 'America/Merida', 'America/Mexico_City', 'America/Moncton',
        'America/Monterrey', 'America/Montevideo', 'America/Montreal', 'America/Nassau', 'America/New_York', 'America/Ojinaga',
        'America/Panama', 'America/Paramaribo', 'America/Phoenix', 'America/Port_of_Spain', 'America/Port-au-Prince',
        'America/Porto_Velho', 'America/Recife', 'America/Regina', 'America/Rio_Branco', 'America/Santo_Domingo',
        'America/Sao_Paulo', 'America/St_Johns', 'America/St_Kitts', 'America/St_Lucia', 'America/St_Vincent',
        'America/Tegucigalpa', 'America/Thule', 'America/Tijuana', 'America/Vancouver', 'America/Whitehorse', 'America/Winnipeg',
        'America/Yellowknife', 'Asia/Aden', 'Asia/Almaty', 'Asia/Amman', 'Asia/Anadyr', 'Asia/Aqtau', 'Asia/Aqtobe', 'Asia/Baghdad',
        'Asia/Bahrain', 'Asia/Baku', 'Asia/Bangkok', 'Asia/Beirut', 'Asia/Bishkek', 'Asia/Choibalsan', 'Asia/Chongqing',
        'Asia/Colombo', 'Asia/Damascus', 'Asia/Dhaka', 'Asia/Dubai', 'Asia/Dushanbe', 'Asia/Harbin', 'Asia/Ho_Chi_Minh',
        'Asia/Hong_Kong', 'Asia/Hovd', 'Asia/Irkutsk', 'Asia/Jakarta', 'Asia/Jayapura', 'Asia/Jerusalem', 'Asia/Kabul',
        'Asia/Kamchatka', 'Asia/Karachi', 'Asia/Kashgar', 'Asia/Kolkata', 'Asia/Krasnoyarsk', 'Asia/Kuala_Lumpur', 'Asia/Kuching',
        'Asia/Kuwait', 'Asia/Macau', 'Asia/Magadan', 'Asia/Makassar', 'Asia/Manila', 'Asia/Muscat', 'Asia/Nicosia', 'Asia/Novokuznetsk',
        'Asia/Novosibirsk', 'Asia/Omsk', 'Asia/Oral', 'Asia/Phnom_Penh', 'Asia/Pontianak', 'Asia/Qatar', 'Asia/Qyzylorda', 'Asia/Riyadh',
        'Asia/Sakhalin', 'Asia/Seoul', 'Asia/Shanghai', 'Asia/Singapore', 'Asia/Taipei', 'Asia/Tashkent', 'Asia/Tbilisi', 'Asia/Tehran',
        'Asia/Thimphu', 'Asia/Tokyo', 'Asia/Ulaanbaatar', 'Asia/Urumqi', 'Asia/Vientiane', 'Asia/Vladivostok', 'Asia/Yakutsk',
        'Asia/Yekaterinburg', 'Asia/Yerevan', 'Atlantic/Azores', 'Atlantic/Bermuda', 'Atlantic/Canary', 'Atlantic/Cape_Verde',
        'Atlantic/Madeira', 'Atlantic/Reykjavik', 'Australia/Adelaide', 'Australia/Brisbane', 'Australia/Darwin', 'Australia/Hobart',
        'Australia/Melbourne', 'Australia/Perth', 'Australia/Sydney', 'Chile/Santiago', 'Europe/Amsterdam', 'Europe/Andorra',
        'Europe/Athens', 'Europe/Belgrade', 'Europe/Berlin', 'Europe/Bratislava', 'Europe/Brussels', 'Europe/Bucharest', 'Europe/Budapest',
        'Europe/Chisinau', 'Europe/Copenhagen', 'Europe/Dublin', 'Europe/Gibraltar', 'Europe/Helsinki', 'Europe/Istanbul',
        'Europe/Kaliningrad', 'Europe/Kiev', 'Europe/Lisbon', 'Europe/Ljubljana', 'Europe/London', 'Europe/Luxembourg', 'Europe/Madrid',
        'Europe/Malta', 'Europe/Mariehamn', 'Europe/Minsk', 'Europe/Monaco', 'Europe/Moscow', 'Europe/Oslo', 'Europe/Paris',
        'Europe/Prague', 'Europe/Riga', 'Europe/Rome', 'Europe/Samara', 'Europe/San_Marino', 'Europe/Sarajevo', 'Europe/Simferopol',
        'Europe/Skopje', 'Europe/Sofia', 'Europe/Stockholm', 'Europe/Tallinn', 'Europe/Tirane', 'Europe/Uzhgorod', 'Europe/Vaduz',
        'Europe/Vatican', 'Europe/Vienna', 'Europe/Vilnius', 'Europe/Volgograd', 'Europe/Warsaw', 'Europe/Yekaterinburg', 'Europe/Zagreb',
        'Europe/Zaporozhye', 'Europe/Zurich', 'Indian/Antananarivo', 'Indian/Comoro', 'Indian/Mahe', 'Indian/Maldives', 'Indian/Mauritius',
        'Pacific/Auckland', 'Pacific/Chatham', 'Pacific/Efate', 'Pacific/Fiji', 'Pacific/Galapagos', 'Pacific/Guadalcanal', 'Pacific/Honolulu',
        'Pacific/Port_Moresby'
    );

    public function __construct($dbFile = 'SxGeo.dat', $type = self::MODE_FILE)
    {
        $this->fh = fopen($dbFile, 'rb');
        // Сначала убеждаемся, что есть файл базы данных
        $header = fread($this->fh, 32);
        if (substr($header, 0, 3) != 'SxG') {
            throw new \InvalidArgumentException(sprintf('File "%" not exists', $dbFile));
        }
        $info = unpack(
            'Cver/Ntime/Ctype/Ccharset/Cb_idx_len/nm_idx_len/nrange/Ndb_items/Cid_len/nmax_region/nmax_city/Nregion_size/Ncity_size',
            substr($header, 3)
        );
        if ($info['b_idx_len'] * $info['m_idx_len'] * $info['range'] * $info['db_items'] * $info['time'] * $info['id_len'] == 0) {
            throw new \InvalidArgumentException(sprintf('File "%" in wrong format', $dbFile));
        }
        $this->bIdxStr = fread($this->fh, $info['b_idx_len'] * 4);
        $this->mIdxStr = fread($this->fh, $info['m_idx_len'] * 4);
        $this->range = $info['range'];
        $this->bIdxLen = $info['b_idx_len'];
        $this->mIdxLen = $info['m_idx_len'];
        $this->dbItems = $info['db_items'];
        $this->idLength = $info['id_len'];
        $this->blockLength = 3 + $this->idLength;
        $this->maxRegion = $info['max_region'];
        $this->maxCty = $info['max_city'];
        $this->batchMode = $type & self::MODE_BATCH;
        $this->memoryMode = $type & self::MODE_MEMORY;
        $this->dbBegin = ftell($this->fh);
        if ($this->batchMode) { // Значительное ускорение блока
            $this->bIdxArr = array_values(unpack("N*", $this->bIdxStr)); // Быстрее в 5 раз, чем с циклом
            unset ($this->bIdxStr);
            $this->mIdxArr = str_split($this->mIdxStr, 4); // Быстрее в 5 раз чем с циклом
            unset ($this->mIdxStr);
        }
        if ($this->memoryMode) {
            $this->db = fread($this->fh, $this->dbItems * $this->blockLength);
            $this->regionsDb = fread($this->fh, $info['region_size']);
            $this->citiesDb = fread($this->fh, $info['city_size']);
        }
        $this->info['regions_begin'] = $this->dbBegin + $this->dbItems * $this->blockLength;
        $this->info['cities_begin'] = $this->info['regions_begin'] + $info['region_size'];
    }

    public function supportsCityGeocoding()
    {
        return (Boolean)$this->maxCty;
    }

    public function getCountryIsoCode($ip)
    {
        $seek = $this->getNum($ip);
        if ($seek > 0) {
            return self::$cc2iso[$seek];
        }

        return null;
    }

    public function getCity($ip)
    {
        $seek = $this->getNum($ip);
        if ($seek > 0) {
            $city = $this->parseCity($seek);
            $city = $this->parseRegion($city['regid'], $city);

            return $city;
        }

        return null;
    }

    protected function searchIdx($ipn, $min, $max)
    {
        if ($this->batchMode) {
            while ($max - $min > 8) {
                $offset = ($min + $max) >> 1;
                if ($ipn > $this->mIdxArr[$offset]) {
                    $min = $offset;
                } else {
                    $max = $offset;
                }
            }
            while ($ipn > $this->mIdxArr[$min] && $min++ < $max) {
            };
        } else {
            while ($max - $min > 8) {
                $offset = ($min + $max) >> 1;
                if ($ipn > substr($this->mIdxStr, $offset * 4, 4)) {
                    $min = $offset;
                } else {
                    $max = $offset;
                }
            }
            while ($ipn > substr($this->mIdxStr, $min * 4, 4) && $min++ < $max) {
            };
        }

        return $min;
    }

    protected function searchDb($str, $ipn, $min, $max)
    {
        if ($max - $min > 0) {
            $ipn = substr($ipn, 1);
            while ($max - $min > 8) {
                $offset = ($min + $max) >> 1;
                if ($ipn > substr($str, $offset * $this->blockLength, 3)) {
                    $min = $offset;
                } else {
                    $max = $offset;
                }
            }
            while ($ipn >= substr($str, $min * $this->blockLength, 3) && $min++ < $max) {
            };
        } else {
            return hexdec(bin2hex(substr($str, $min * $this->blockLength + 3, 3)));
        }

        return hexdec(bin2hex(substr($str, $min * $this->blockLength - $this->idLength, $this->idLength)));
    }

    protected function getNum($ip)
    {
        $ip1n = (int)$ip; // Первый байт
        if ($ip1n == 0 || $ip1n == 10 || $ip1n == 127 || $ip1n >= $this->bIdxLen || false === ($ipn = ip2long(
                $ip
            ))
        ) {
            return false;
        }
        $ipn = pack('N', $ipn);
        // Находим блок данных индексе первых байт
        if ($this->batchMode) {
            $blocks = array('min' => $this->bIdxArr[$ip1n - 1], 'max' => $this->bIdxArr[$ip1n]);
        } else {
            $blocks = unpack("Nmin/Nmax", substr($this->bIdxStr, ($ip1n - 1) * 4, 8));
        }
        // Ищем блок в основном индексе
        $part = $this->searchIdx($ipn, floor($blocks['min'] / $this->range), floor($blocks['max'] / $this->range) - 1);
        // Нашли номер блока в котором нужно искать IP, теперь находим нужный блок в БД
        $min = $part > 0 ? $part * $this->range : 0;
        $max = $part > $this->mIdxLen ? $this->dbItems : ($part + 1) * $this->range;
        // Нужно проверить чтобы блок не выходил за пределы блока первого байта
        if ($min < $blocks['min']) {
            $min = $blocks['min'];
        }
        if ($max > $blocks['max']) {
            $max = $blocks['max'];
        }
        $len = $max - $min;
        // Находим нужный диапазон в БД
        if ($this->memoryMode) {
            return $this->searchDb($this->db, $ipn, $min, $max);
        }
        fseek($this->fh, $this->dbBegin + $min * $this->blockLength);

        return $this->searchDb(fread($this->fh, $len * $this->blockLength), $ipn, 0, $len - 1);
    }

    protected function parseCity($seek)
    {
        if ($this->memoryMode) {
            $raw = substr($this->citiesDb, $seek, $this->maxCty);
        } else {
            fseek($this->fh, $this->info['cities_begin'] + $seek);
            $raw = fread($this->fh, $this->maxCty);
        }

        $city = unpack('Nregid/Ccc/a2fips/Nlat/Nlon', $raw);
        $city['country'] = self::$cc2iso[$city['cc']];
        $city['lat'] /= 1000000;
        $city['lon'] /= 1000000;
        $c = explode("\0", substr($raw, 15), 2);
        $city['city'] = $c[0];

        return $city;
    }

    protected function parseRegion($regionSeek, $city)
    {
        if ($regionSeek > 0) {
            if ($this->memoryMode) {
                $region = explode("\0", substr($this->regionsDb, $regionSeek, $this->maxRegion));
            } else {
                fseek($this->fh, $this->info['regions_begin'] + $regionSeek);
                $region = explode("\0", fread($this->fh, $this->maxRegion));
            }
            $city['region_name'] = $region[0];
            $city['timezone'] = '';
            //FIXME
            //$city['timezone'] = self::$tz[$region[1]];
        } else {
            $city['region_name'] = '';
            $city['timezone'] = '';
        }

        return $city;
    }
}

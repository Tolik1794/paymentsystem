<?php

namespace payment\PaymentSystem\Portmone\libs;

use payment\PaymentSystem\Portmone\exceptions\PortmoneException;

class PortmoneHelper
{
    /**
     * For supporting also comma in values
     * @param $value
     * @return float
     */
    public static function toFloat($value)
    {
        $value = preg_replace('/[^\d\,\.\-]/', '', $value);
        $value = str_replace(',', '.', $value);
        return floatval($value);
    }

    /**
     * Encode values for inserting into html tags
     * @param $content
     * @param string $encoding
     * @param bool $doubleEncode
     * @return string
     */
    public static function encode($content, $encoding = 'UTF-8', $doubleEncode = true)
    {
        return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, $encoding, $doubleEncode);
    }

    /**
     * Check and parse XML into simple objects tree
     * @param $string
     * @return object
     * @throws PortmoneException
     */
    public static function parseXml($string)
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($string, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (false !== $xml) {
            // convert to simple data object
            $data = json_decode(json_encode($xml));
            // then cast right types of values
            self::castFormats($data);
            return $data;
        } else {
            throw new PortmoneException('Can\'t parse response XML', PortmoneException::PARSE_ERROR);
        }
    }

    /**
     * Convert array data (e.g. POST data) to simple objects tree for unified processing
     * @param $array
     * @return mixed
     */
    public static function parseArray($array)
    {
        if (empty($array)) {
            return new \stdClass(); // empty object
        }
        // convert to simple data object
        $data = json_decode(json_encode($array));
        // then cast right types of values
        self::castFormats($data);
        return $data;
    }

    /**
     * Convert float and integer values from string to appropriate types
     * @param $data
     */
    protected static function castFormats(&$data)
    {
        if (is_object($data) || is_array($data)) {
            foreach ($data as &$value) {
                if (is_object($value) || is_array($value)) {
                    self::castFormats($value);
                } elseif ((string)$value === (string)(int)$value) {
                    $value = (int)$value;
                } elseif ((string)$value === (string)(float)$value) {
                    $value = (float)$value;
                } elseif (preg_match('/^[\+\-]?\d+\.+\d+$/', $value)) { // for values as "123.00"
                    $value = (float)$value;
                }
            }
        }
    }
}
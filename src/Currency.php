<?php

namespace LeeArtem\Converter;

use LeeArtem\Converter\Interface\ConverterInterface;
use Illuminate\Support\Facades\Cache;

class Currency implements ConverterInterface
{
    private static $instance;

    private static $data;
    private static $from;
    private static $to;
    private static $amount;
    private static $final;

    private function __construct()
    {
        self::$data = self::getData();
    }

    private function __clone()
    {
    }

    private static function getData()
    {
        $response = self::getFromCache() ?: self::getFromApi();
        try {
            if($response->success === true) return $response;
            return self::getData();
        } catch(Exception $e) {
            self::flush();
            throw new \Exception("API is not available.");
        }
    }

    public static function getFromCache()
    {
        return Cache::get('rates');
    }

    public static function getFromApi()
    {
        $req_url = 'https://api.exchangerate.host/latest';
        $response = json_decode(file_get_contents($req_url));
        self::updateCache($response);
        return $response;
    }

    public static function updateCache($response)
    {
        Cache::put('rates', $response, $seconds = config('converter.timeout'));
    }

    public static function flush()
    {
        Cache::forget('rates');
    }

    public static function from(string $from)
    {
        if (self::$instance===null) {
            self::$instance = new static;
        }
        static::$from = $from;
        return self::$instance; 
    }

    public static function to(string $to)
    {
        static::$to = $to;
        return self::$instance; 
    }

    public static function get(float $amount, int $decimals = 2): float
    {
        try {
            static::$amount = $amount;
            return self::convert($decimals);
        } catch (\Throwable $th) {
            throw new \Exception("Some parameters have not been specified.");
        }
    }

    public static function format(float $amount, int $decimals = 2, string $devider = " "): string
    {
        static::$amount = $amount;
        self::convert($decimals);
        return number_format(self::$final, $decimals, ".", $devider);
    }

    public static function convert($decimals): float
    {
        $fromCurrency = static::$from;
        $toCurrency = static::$to;
        
        $eurAmount = static::$amount / self::$data->rates->$fromCurrency;
        self::$final = $eurAmount * self::$data->rates->$toCurrency;
        return round(self::$final,$decimals);
    }

}

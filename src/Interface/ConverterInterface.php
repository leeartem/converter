<?php

namespace LeeArtem\Converter\Interface;

interface ConverterInterface
{
    public static function from(string $from);

    public static function to(string $to);

    public static function get(float $amount);
}
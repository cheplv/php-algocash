<?php
namespace AlgorithmicCash;

class PaymentUrl {
    const PAYIN_URL      = "https://api.algorithmic.cash/request_payin.php";
    const PAYIN_URL_V2   = "https://api.algorithmic.cash/request_payin_v2.php";
    const PAYOUT_URL     = "https://api.algorithmic.cash/request_payout.php";
    const PAYBALANCE_URL = "https://api.algorithmic.cash/request_balance.php";

    public static function buildPayInUrl(array $params, string $url = "") {
        return (($url != "") ? $url : self::PAYIN_URL) . '?' . http_build_query($params);
    }

    public static function buildPayOutUrl(array $params, string $url = "") {
        return (($url != "") ? $url : self::PAYOUT_URL) . '?' . http_build_query($params);
    }

    public static function buildPayBalanceUrl(array $params, string $url = "") {
        return (($url != "") ? $url : self::PAYBALANCE_URL) . '?' . http_build_query($params);
    }
}

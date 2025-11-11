<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Currency\CurrencyTable;
use Bitrix\Main\Context;
use Bitrix\Currency\CurrencyLangTable;


class OtusShowCurrencyComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams['CURRENCY'] = trim($arParams['CURRENCY'] ?? '');
        $arParams['CACHE_TIME'] = isset($arParams['CACHE_TIME']) ? (int)$arParams['CACHE_TIME'] : 3600;

        return $arParams;
    }

    public function executeComponent()
    {
        try {
            if (!$this->checkModules()) {
                return;
            }

            if ($this->startResultCache()) {
                $this->getCurrencyData();
                $this->includeComponentTemplate();
            }
        } catch (Exception $e) {
            $this->abortResultCache();
            $this->arResult['ERROR'] = $e->getMessage();
        }
    }

    protected function checkModules()
    {
        if (!Loader::includeModule('currency')) {
            $this->arResult['ERROR'] = 'Модуль валют не установлен';
            return false;
        }
        return true;
    }

    protected function getCurrencyData()
    {
        if (empty($this->arParams['CURRENCY'])) {
            throw new Exception('Валюта не выбрана');
        }

        $currency = CurrencyTable::getList([
            'filter' => ['=CURRENCY' => $this->arParams['CURRENCY']],
            'select' => ['CURRENCY', 'AMOUNT', 'AMOUNT_CNT', 'SORT', 'DATE_UPDATE']
        ])->fetch();

        if (!$currency) {
            throw new Exception('Валюта не найдена');
        }
        $result = CurrencyLangTable::getList([
            'select' => ['*'],
            'filter' => ['=CURRENCY' => $currency['CURRENCY']]
        ]);

        while ($currencyLang = $result->fetch()) {
            $curfullname[$currencyLang['LID']] = $currencyLang['FULL_NAME'];
        }

        $this->arResult['CURRENCY'] = [
            'CODE' => $currency['CURRENCY'],
            'CURRENCY_NAME' => $curfullname['ru'],
            'AMOUNT' => $currency['AMOUNT'],
            'AMOUNT_CNT' => $currency['AMOUNT_CNT'],
            'RATE' => $currency['AMOUNT'] / $currency['AMOUNT_CNT'],
            'DATE_UPDATE' => $currency['DATE_UPDATE'],
            'FORMATTED_RATE' => $this->formatRate($currency['AMOUNT'], $currency['AMOUNT_CNT'])
        ];
    }

    protected function formatRate($amount, $amountCnt)
    {
        if ($amountCnt == 1) {
            return number_format($amount, 4, '.', ' ');
        } else {
            return $amountCnt . ' = ' . number_format($amount, 4, '.', ' ');
        }
    }
}

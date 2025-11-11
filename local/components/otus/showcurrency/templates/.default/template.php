<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<div class="otus-currency-component">
    <?php if (isset($arResult['ERROR'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialcharsbx($arResult['ERROR']) ?>
        </div>
    <?php elseif (isset($arResult['CURRENCY'])): ?>
        <div class="currency-info">
        <h3>Курс валюты: <?= htmlspecialcharsbx($arResult['CURRENCY']['CODE']) ?> - <?= $arResult['CURRENCY']['CURRENCY_NAME'] ?></h3>
            <div class="currency-rate">
                <strong><?= $arResult['CURRENCY']['FORMATTED_RATE'] ?> ₽</strong>
            </div>
            <?php if ($arResult['CURRENCY']['DATE_UPDATE']): ?>
                <div class="currency-date">
                    Обновлено: <?= FormatDate('d.m.Y H:i', MakeTimeStamp($arResult['CURRENCY']['DATE_UPDATE'])) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Валбта не найдены
        </div>
    <?php endif; ?>
</div>
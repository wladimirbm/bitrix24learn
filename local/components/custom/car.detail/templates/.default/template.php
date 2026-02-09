<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

//$this->IncludeComponentLang('template.php');

if ($arResult['HAS_ERROR'] ?? false): ?>
    <div style="padding: 40px; text-align: center; color: #e74c3c;">
        <h3><?= GetMessage('ERROR_LOADING') ?></h3>
        <p><?= $arResult['ERROR'] ?></p>
    </div>
    <?php return;
endif;
?>

<div class="car-detail-popup" style="display: flex; gap: 30px; font-family: Arial, sans-serif; color: #333;">
    
    <!-- ЛЕВАЯ КОЛОНКА - Информация об авто -->
    <div style="flex: 1; min-width: 300px;">
        <h2 style="margin-top: 0; color: #1d539f; border-bottom: 2px solid #1d539f; padding-bottom: 10px;">
            <?= htmlspecialcharsbx($arResult['CAR']['BRAND']) ?> 
            <?= htmlspecialcharsbx($arResult['CAR']['MODEL']) ?> - 
            <?= htmlspecialcharsbx($arResult['CAR']['NUMBER']) ?>
        </h2>
        
        <p style="color: #666; font-style: italic;">
            <?= GetMessage('CAR_DETAIL_OWNER') ?>: <?= htmlspecialcharsbx($arResult['CAR']['OWNER_NAME']) ?>
        </p>
        
        <!-- Статус -->
        <div style="background: <?= $arResult['CAR']['STATUS_COLOR'] ?>; 
                    color: white; 
                    padding: 5px 12px; 
                    border-radius: 15px; 
                    display: inline-block; 
                    font-weight: bold; 
                    margin-bottom: 20px;">
            <?= htmlspecialcharsbx($arResult['CAR']['STATUS_TEXT']) ?>
        </div>
        
        <!-- Характеристики -->
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
            <div style="margin-bottom: 12px;">
                <strong style="color: #555;"><?= GetMessage('CAR_DETAIL_YEAR') ?>:</strong><br>
                <?= $arResult['CAR']['YEAR'] ? htmlspecialcharsbx($arResult['CAR']['YEAR']) : GetMessage('EMPTY_VALUE') ?>
            </div>
            
            <?php if (!empty($arResult['CAR']['VIN'])): ?>
            <div style="margin-bottom: 12px;">
                <strong style="color: #555;">VIN:</strong><br>
                <?= htmlspecialcharsbx($arResult['CAR']['VIN']) ?>
            </div>
            <?php endif; ?>
            
            <div style="margin-bottom: 12px;">
                <strong style="color: #555;"><?= GetMessage('CAR_DETAIL_COLOR') ?>:</strong><br>
                <?= $arResult['CAR']['COLOR'] ? htmlspecialcharsbx($arResult['CAR']['COLOR']) : GetMessage('EMPTY_VALUE') ?>
            </div>
            
            <div style="margin-bottom: 12px;">
                <strong style="color: #555;"><?= GetMessage('CAR_DETAIL_MILEAGE') ?>:</strong><br>
                <?= $arResult['CAR']['MILEAGE'] ? 
                    number_format($arResult['CAR']['MILEAGE'], 0, '', ' ') . ' ' . GetMessage('CAR_DETAIL_KM') : 
                    GetMessage('EMPTY_VALUE') ?>
            </div>
            
            <div>
                <strong style="color: #555;"><?= GetMessage('CAR_DETAIL_STATUS') ?>:</strong><br>
                <?= $arResult['CAR']['ACTIVE_DEALS_COUNT'] > 0 ? 
                    '<span style="color: #e74c3c;">' . $arResult['CAR']['STATUS_DESCRIPTION'] . '</span>' : 
                    '<span style="color: #27ae60;">' . $arResult['CAR']['STATUS_DESCRIPTION'] . '</span>' ?>
                
                <?php if ($arResult['CAR']['ACTIVE_DEALS_COUNT'] > 0): ?>
                    <span style="font-size: 0.9em; color: #777;">
                        (<?= $arResult['CAR']['ACTIVE_DEALS_COUNT'] ?> 
                        <?= pluralForm(
                            $arResult['CAR']['ACTIVE_DEALS_COUNT'], 
                            [GetMessage('PLURAL_DEAL_1'), GetMessage('PLURAL_DEAL_2'), GetMessage('PLURAL_DEAL_5')]
                        ) ?>)
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- ПРАВАЯ КОЛОНКА - Активные сделки -->
    <div style="flex: 1; min-width: 350px;">
        <h3 style="margin-top: 0; color: #1d539f; border-bottom: 2px solid #1d539f; padding-bottom: 10px;">
            <?= GetMessage('CAR_RELATED_DEALS') ?>
            <?php if (!empty($arResult['DEALS'])): ?>
                <span style="font-size: 0.8em; color: #666;">
                    (<?= str_replace('#COUNT#', count($arResult['DEALS']), GetMessage('CAR_DEALS_COUNT')) ?>)
                </span>
            <?php endif; ?>
        </h3>
        
        <?php if (empty($arResult['DEALS'])): ?>
            <div style="text-align: center; padding: 40px 20px; color: #777; font-style: italic;">
                <?= GetMessage('CAR_NO_DEALS') ?>
            </div>
        <?php else: ?>
            <div style="max-height: 400px; overflow-y: auto;">
                <?php foreach ($arResult['DEALS'] as $deal): ?>
                <div style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; 
                            padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    
                    <!-- Заголовок сделки -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h4 style="margin: 0;">
                            <a href="/crm/deal/details/<?= $deal['ID'] ?>/" 
                               target="_blank"
                               style="color: #1d539f; text-decoration: none; font-size: 1.1em;">
                                <?= htmlspecialcharsbx($deal['TITLE']) ?>
                            </a>
                        </h4>
                        <span style="background: #f1f8ff; color: #1d539f; padding: 3px 8px; 
                                    border-radius: 12px; font-size: 0.85em;">
                            <?= htmlspecialcharsbx($deal['STAGE_NAME']) ?>
                        </span>
                    </div>
                    
                    <!-- Детали сделки -->
                    <div style="color: #555; font-size: 0.9em;">
                        <div style="margin-bottom: 5px;">
                            <strong><?= GetMessage('DEAL_CREATE_DATE') ?>:</strong> 
                            <?= FormatDate('d.m.Y, H:i', MakeTimeStamp($deal['DATE_CREATE'])) ?>
                        </div>
                        
                        <div style="margin-bottom: 5px;">
                            <strong><?= GetMessage('DEAL_AMOUNT') ?>:</strong> 
                            <?= $deal['OPPORTUNITY'] > 0 ? 
                                number_format($deal['OPPORTUNITY'], 0, '', ' ') . ' ₽' : 
                                '<span style="color: #777;">' . GetMessage('ZERO_AMOUNT') . '</span>' ?>
                        </div>
                        
                        <div style="margin-bottom: 10px;">
                            <strong><?= GetMessage('DEAL_RESPONSIBLE') ?>:</strong> 
                            <a href="/company/personal/user/<?= $deal['ASSIGNED_BY_ID'] ?>/" 
                               target="_blank"
                               style="color: #555;">
                                <?= htmlspecialcharsbx($deal['ASSIGNED_BY_NAME']) ?>
                            </a>
                        </div>
                        
                        <!-- Запчасти -->
                        <?php if (!empty($deal['PRODUCT_ROWS'])): ?>
                        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e0e0e0;">
                            <strong style="color: #555;"><?= GetMessage('DEAL_PARTS') ?>:</strong>
                            <ul style="margin: 5px 0 0 0; padding-left: 20px;">
                                <?php foreach ($deal['PRODUCT_ROWS'] as $product): ?>
                                <li style="margin-bottom: 3px;">
                                    <?= htmlspecialcharsbx($product['NAME']) ?> 
                                    <span style="color: #777; font-size: 0.9em;">
                                        ×<?= $product['QUANTITY'] ?> <?= GetMessage('DEAL_PIECES') ?>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
function pluralForm($number, $forms) {
    $cases = [2, 0, 1, 1, 1, 2];
    return $forms[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
}
?>
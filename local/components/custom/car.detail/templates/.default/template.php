<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if ($arResult['HAS_ERROR'] ?? false): ?>
    <div style="padding: 40px; text-align: center; color: #e74c3c;">
        <h3>Ошибка</h3>
        <p><?= $arResult['ERROR'] ?></p>
    </div>
    <?php return;
endif;

// Функция для цвета стадии
function getStageColor($stageId)
{
    $colors = [
        'C1:NEW' => '#3498db',           // Синий - Приёмка
        'C1:PREPARATION' => '#9b59b6',   // Фиолетовый - Диагностика
        'C1:PREPAYMENT_INVOICE' => '#f39c12', // Оранжевый - Ожидание запчастей
        'C1:EXECUTING' => '#e74c3c',     // Красный - Ремонт
        'C1:FINAL_INVOICE' => '#2ecc71'  // Зеленый - Проверка
    ];
    
    return $colors[$stageId] ?? '#95a5a6'; // Серый по умолчанию
}
?>

<div style="display: flex; gap: 30px; min-height: 500px; font-family: Arial, sans-serif;">
    
    <!-- Левая колонка - информация об авто -->
    <div style="flex: 1; min-width: 300px;">
        <h2 style="margin-top: 0; color: #1d539f; border-bottom: 2px solid #1d539f; padding-bottom: 10px;">
            <?= $arResult['CAR']['BRAND'] ?> 
            <?= $arResult['CAR']['MODEL'] ?> - 
            <?= $arResult['CAR']['NUMBER'] ?>
        </h2>
        
        <p style="color: #666; font-style: italic;">
            Владелец: <?= $arResult['CAR']['OWNER_NAME'] ?>
        </p>
        
        <!-- Статус авто -->
        <div style="background: <?= $arResult['CAR']['STATUS_COLOR'] ?>; 
                    color: white; 
                    padding: 5px 12px; 
                    border-radius: 15px; 
                    display: inline-block; 
                    font-weight: bold; 
                    margin-bottom: 20px;">
            <?= $arResult['CAR']['STATUS_TEXT'] ?>
            <?php if ($arResult['CAR']['ACTIVE_DEALS_COUNT'] > 0): ?>
                <span style="font-size: 0.8em; opacity: 0.9;">
                    (<?= $arResult['CAR']['ACTIVE_DEALS_COUNT'] ?> активн.)
                </span>
            <?php endif; ?>
        </div>
        
        <!-- Характеристики авто -->
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
            <div style="margin-bottom: 12px;">
                <strong style="color: #555;">Год выпуска:</strong><br>
                <?= $arResult['CAR']['YEAR'] ?>
            </div>
            
            <?php if (!empty($arResult['CAR']['VIN']) && $arResult['CAR']['VIN'] != '—'): ?>
            <div style="margin-bottom: 12px;">
                <strong style="color: #555;">VIN:</strong><br>
                <?= $arResult['CAR']['VIN'] ?>
            </div>
            <?php endif; ?>
            
            <div style="margin-bottom: 12px;">
                <strong style="color: #555;">Цвет:</strong><br>
                <?= $arResult['CAR']['COLOR'] ?>
            </div>
            
            <div style="margin-bottom: 12px;">
                <strong style="color: #555;">Пробег:</strong><br>
                <?= $arResult['CAR']['MILEAGE'] ?>
            </div>
            
            <div>
                <strong style="color: #555;">Состояние:</strong><br>
                <?= $arResult['CAR']['ACTIVE_DEALS_COUNT'] > 0 ? 
                    '<span style="color: #e74c3c; font-weight: bold;">' . $arResult['CAR']['STATUS_DESCRIPTION'] . '</span>' : 
                    '<span style="color: #27ae60; font-weight: bold;">' . $arResult['CAR']['STATUS_DESCRIPTION'] . '</span>' ?>
            </div>
        </div>
    </div>
    
    <!-- Правая колонка - все активные сделки -->
    <div style="flex: 1; min-width: 350px;">
        <h3 style="margin-top: 0; color: #1d539f; border-bottom: 2px solid #1d539f; padding-bottom: 10px;">
            Все активные сделки
            <?php if (!empty($arResult['DEALS'])): ?>
                <span style="font-size: 0.8em; color: #666;">
                    (<?= count($arResult['DEALS']) ?>)
                </span>
            <?php endif; ?>
        </h3>
        
        <?php if (empty($arResult['DEALS'])): ?>
            <div style="text-align: center; padding: 40px 20px; color: #777; font-style: italic;">
                Нет активных сделок по этому автомобилю
            </div>
        <?php else: ?>
            <div style="max-height: 500px; overflow-y: auto; padding-right: 5px;">
                <?php
                echo "<pre>";print_r($arResult['DEALS']);echo "</pre>";
                foreach ($arResult['DEALS'] as $deal): 
                    $stageColor = getStageColor($deal['STAGE_ID']);
                ?>
                <div style="background: white; border-left: 4px solid <?= $stageColor ?>; 
                            border: 1px solid #e0e0e0; border-radius: 8px; 
                            padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    
                    <!-- Заголовок и стадия -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 5px 0;">
                                <a href="/crm/deal/details/<?= $deal['ID'] ?>/" 
                                   target="_blank"
                                   style="color: #1d539f; text-decoration: none; font-size: 1.1em;">
                                    <?= $deal['TITLE'] ?>
                                </a>
                            </h4>
                            <div style="font-size: 0.85em; color: #666;">
                                <strong>ID:</strong> <?= $deal['ID'] ?> | 
                                <strong>Создана:</strong> <?= $deal['DATE_CREATE'] ?>
                            </div>
                        </div>
                        
                        <div style="margin-left: 10px; text-align: right;">
                            <span style="background: <?= $stageColor ?>; color: white; padding: 4px 10px; 
                                        border-radius: 15px; font-size: 0.85em; font-weight: bold; 
                                        display: inline-block; min-width: 120px; text-align: center;">
                                <?= $deal['STAGE_NAME'] ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Основная информация -->
                    <div style="color: #555; font-size: 0.9em; background: #f9f9f9; 
                                padding: 10px; border-radius: 5px; margin-top: 10px;">
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <div>
                                <strong>Сумма:</strong><br>
                                <span style="font-size: 1.1em; font-weight: bold; color: #2c3e50;">
                                    <?= $deal['OPPORTUNITY'] ?>
                                </span>
                            </div>
                            
                            <div style="text-align: right;">
                                <strong>Ответственный:</strong><br>
                                <?= $deal['ASSIGNED_BY_NAME'] ?>
                            </div>
                        </div>
                        
                        <!-- Запчасти -->
                        <?php if (!empty($deal['PRODUCT_ROWS'])): ?>
                        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ddd;">
                            <strong style="color: #555;">Запчасти:</strong>
                            <div style="margin-top: 5px;">
                                <?php foreach ($deal['PRODUCT_ROWS'] as $product): ?>
                                <div style="display: inline-block; background: white; border: 1px solid #e0e0e0; 
                                            border-radius: 4px; padding: 3px 8px; margin: 0 5px 5px 0; 
                                            font-size: 0.85em;">
                                    <?= $product['NAME'] ?>
                                    <span style="color: #777; font-weight: bold; margin-left: 3px;">
                                        ×<?= $product['QUANTITY'] ?> шт.
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Ссылка на сделку -->
                    <div style="margin-top: 10px; text-align: right;">
                        <a href="/crm/deal/details/<?= $deal['ID'] ?>/" 
                           target="_blank"
                           style="color: #7f8c8d; font-size: 0.85em; text-decoration: none;">
                            Перейти к сделке →
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Статистика -->
            <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; 
                        font-size: 0.9em; color: #555;">
                <strong>Всего сделок:</strong> <?= count($arResult['DEALS']) ?> | 
                <strong>Общая сумма:</strong> 
                <?php
                $totalAmount = 0;
                foreach ($arResult['DEALS'] as $deal) {
                    preg_match('/[\d\s]+/', $deal['OPPORTUNITY'], $matches);
                    if ($matches) {
                        $amount = (int)str_replace(' ', '', $matches[0]);
                        $totalAmount += $amount;
                    }
                }
                echo number_format($totalAmount, 0, '', ' ') . ' ₽';
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>
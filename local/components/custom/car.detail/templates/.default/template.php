<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if ($arResult['HAS_ERROR'] ?? false): ?>
    <div style="padding: 40px; text-align: center; color: #FF5752; font-family: 'Segoe UI', Arial, sans-serif;">
        <h3 style="margin-bottom: 15px;">Ошибка</h3>
        <p><?= $arResult['ERROR'] ?></p>
    </div>
    <?php return;
endif;

function getStageColor($stageId)
{
    $colors = [
        'C1:NEW' => '#1f86ff',
        'C1:PREPARATION' => '#30afff',
        'C1:PREPAYMENT_INVOICE' => '#00c0d5',
        'C1:EXECUTING' => '#31c469',
        'C1:FINAL_INVOICE' => '#faaa08',
        'C1:WON' => '#7bd500',
        'C1:LOSE' => '#FF5752',
        'C1:APOLOGY' => '#FF5752'
    ];
    
    return $colors[$stageId] ?? '#d3d7dc';
}
?>

<style>
.popup-window-content div {
    padding: 0 !important;
}
</style>

<div style="width: 1000px; padding: 30px; font-family: 'Segoe UI', Arial, sans-serif; font-size: 18px; background: #f5f7f8;">
    
    <!-- Заголовок -->
    <div style="margin-bottom: 25px;">
        <h2 style="margin: 0; color: #0B66C3; font-size: 22px; font-weight: 600; padding-bottom: 10px; border-bottom: 1px solid #e6e9ed;">
            Информация об автомобиле
        </h2>
    </div>
    
    <div style="display: flex; gap: 30px; min-height: 500px;">
        
        <!-- Левая колонка - информация об авто -->
        <div style="flex: 1; min-width: 400px; background: white; border-radius: 6px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
            <div style="margin-bottom: 25px;">
                <div style="font-size: 20px; font-weight: 700; color: #424956; margin-bottom: 5px;">
                    <?= $arResult['CAR']['BRAND'] ?> <?= $arResult['CAR']['MODEL'] ?>
                </div>
                <div style="color: #828b95; font-size: 16px;">
                    <?= $arResult['CAR']['NUMBER'] ?> • <?= $arResult['CAR']['OWNER_NAME'] ?>
                </div>
            </div>
            
            <!-- Статус авто -->
            <div style="background: <?= $arResult['CAR']['STATUS_COLOR'] ?>; 
                        color: white; 
                        padding: 8px 16px; 
                        border-radius: 15px; 
                        display: inline-flex; 
                        align-items: center; 
                        font-weight: 600; 
                        font-size: 16px; 
                        margin-bottom: 25px;">
                <?= $arResult['CAR']['STATUS_TEXT'] ?>
                <?php if ($arResult['CAR']['ACTIVE_DEALS_COUNT'] > 0): ?>
                    <span style="margin-left: 10px; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 10px; font-size: 14px;">
                        <?= $arResult['CAR']['ACTIVE_DEALS_COUNT'] ?> активн.
                    </span>
                <?php endif; ?>
            </div>
            
            <!-- Характеристики авто -->
            <div style="border-top: 1px solid #edeef0; padding-top: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                    <div style="color: #828b95; font-size: 16px;">Год выпуска</div>
                    <div style="color: #424956; font-weight: 500; text-align: right;"><?= $arResult['CAR']['YEAR'] ?></div>
                </div>
                
                <?php if (!empty($arResult['CAR']['VIN']) && $arResult['CAR']['VIN'] != '—'): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                    <div style="color: #828b95; font-size: 16px;">VIN</div>
                    <div style="color: #424956; font-weight: 500; text-align: right; font-family: monospace;"><?= $arResult['CAR']['VIN'] ?></div>
                </div>
                <?php endif; ?>
                
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                    <div style="color: #828b95; font-size: 16px;">Цвет</div>
                    <div style="color: #424956; font-weight: 500; text-align: right;"><?= $arResult['CAR']['COLOR'] ?></div>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                    <div style="color: #828b95; font-size: 16px;">Пробег</div>
                    <div style="color: #424956; font-weight: 500; text-align: right;"><?= $arResult['CAR']['MILEAGE'] ?></div>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                    <div style="color: #828b95; font-size: 16px;">Состояние</div>
                    <div style="color: <?= $arResult['CAR']['ACTIVE_DEALS_COUNT'] > 0 ? '#31c469' : '#31c469' ?>; font-weight: 600; text-align: right;">
                        <?= $arResult['CAR']['STATUS_DESCRIPTION'] ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Правая колонка - все активные сделки -->
        <div style="flex: 1; min-width: 500px;">
            <div style="background: white; border-radius: 6px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="margin-bottom: 25px;">
                    <h3 style="margin: 0 0 10px 0; color: #0B66C3; font-size: 20px; font-weight: 600;">
                        Связанные сделки
                    </h3>
                    <div style="color: #525c69; font-size: 16px;">
                        <?php if (!empty($arResult['DEALS'])): ?>
                            <?= count($arResult['DEALS']) ?> активных сделок
                        <?php else: ?>
                            Нет активных сделок
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (empty($arResult['DEALS'])): ?>
                    <div style="text-align: center; padding: 40px 20px; color: #a8adb4; font-style: italic; border: 2px dashed #edeef0; border-radius: 6px;">
                        Нет активных сделок по этому автомобилю
                    </div>
                <?php else: ?>
                    <div style="max-height: 500px; overflow-y: auto; padding-right: 5px;">
                        <?php foreach ($arResult['DEALS'] as $deal): 
                            $stageColor = getStageColor($deal['STAGE_ID']);
                        ?>
                        <div style="background: white; border: 1px solid #e6e9ed; border-radius: 6px; 
                                    padding: 20px; margin-bottom: 15px; transition: all 0.2s;
                                    border-left: 4px solid <?= $stageColor ?>;">
                            
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                                <div style="flex: 1;">
                                    <div style="margin-bottom: 8px;">
                                        <h4 style="margin: 0;">
                                            <a href="/crm/deal/details/<?= $deal['ID'] ?>/" 
                                               target="_blank"
                                               style="color: #0B66C3; text-decoration: none; font-size: 18px; font-weight: 600;">
                                                <?= $deal['TITLE'] ?>
                                            </a>
                                        </h4>
                                    </div>
                                    
                                    <div style="font-size: 14px; color: #828b95;">
                                        <span style="margin-right: 15px;">ID: <?= $deal['ID'] ?></span>
                                        <span>Создана: <?= $deal['DATE_CREATE'] ?></span>
                                    </div>
                                </div>
                                
                                <div style="margin-left: 15px;">
                                    <span style="background: <?= $stageColor ?>; color: white; padding: 6px 12px; 
                                                border-radius: 15px; font-size: 14px; font-weight: 600; 
                                                display: inline-block; min-width: 140px; text-align: center;">
                                        <?= $deal['STAGE_NAME'] ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div style="background: #f8fafc; border-radius: 6px; padding: 15px; margin-top: 15px;">
                                <div style="display: flex; justify-content: space-between;">
                                    <div>
                                        <div style="color: #828b95; font-size: 14px; margin-bottom: 5px;">Сумма</div>
                                        <div style="font-size: 20px; font-weight: 700; color: #31c469;">
                                            <?= $deal['OPPORTUNITY'] ?>
                                        </div>
                                    </div>
                                    
                                    <div style="text-align: right;">
                                        <div style="color: #828b95; font-size: 14px; margin-bottom: 5px;">Ответственный</div>
                                        <div style="font-weight: 600; color: #424956;">
                                            <?= $deal['ASSIGNED_BY_NAME'] ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($deal['PRODUCT_ROWS'])): ?>
                                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e6e9ed;">
                                    <div style="color: #828b95; font-size: 14px; margin-bottom: 10px;">
                                        Запчасти
                                    </div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                        <?php foreach ($deal['PRODUCT_ROWS'] as $product): ?>
                                        <div style="background: white; border: 1px solid #e6e9ed; 
                                                    border-radius: 4px; padding: 6px 12px; 
                                                    font-size: 14px;">
                                            <?= $product['NAME'] ?>
                                            <span style="color: #828b95; font-weight: 600; margin-left: 6px;">
                                                ×<?= $product['QUANTITY'] ?> шт.
                                            </span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div style="margin-top: 15px; text-align: right;">
                                <a href="/crm/deal/details/<?= $deal['ID'] ?>/" 
                                   target="_blank"
                                   style="color: #828b95; font-size: 15px; text-decoration: none; 
                                          display: inline-flex; align-items: center; padding: 8px 16px;
                                          border: 1px solid #e6e9ed; border-radius: 4px; transition: all 0.2s;
                                          background: white;">
                                    <span style="margin-right: 8px;">Перейти к сделке</span>
                                    <span>→</span>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Статистика -->
                    <div style="margin-top: 25px; padding: 15px; background: #f8fafc; border-radius: 6px; 
                                font-size: 16px; color: #424956; display: flex; justify-content: space-between;">
                        <div>
                            <strong>Всего сделок:</strong> <?= count($arResult['DEALS']) ?>
                        </div>
                        <div>
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
                            echo '<span style="color: #31c469; font-weight: 700; margin-left: 5px;">' . number_format($totalAmount, 0, '', ' ') . ' ₽</span>';
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
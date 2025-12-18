<?php

namespace UserTypes;

class BookingProcedureType
{
    const USER_TYPE = 'iblock_booking';

    public static function GetUserTypeDescription()
    {
        return [
            'PROPERTY_TYPE'        => 'S',
            'USER_TYPE'            => self::USER_TYPE,
            'DESCRIPTION'          => 'Бронирование',
            'GetPropertyFieldHtml' => [self::class, 'GetPropertyFieldHtml'],
            'GetSearchContent'     => [self::class, 'GetSearchContent'],
            'GetAdminListViewHTML' => [self::class, 'GetAdminListViewHTML'],
            'GetPublicEditHTML'    => [self::class, 'GetPropertyFieldHtml'],
            'GetPublicViewHTML'    => [self::class, 'GetPublicViewHTML'],
        ];
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $elementId = $_REQUEST['ID'] ?? 0;
        $procedures = self::getDoctorProcedures($elementId);

        $html = '';
        foreach ($procedures as $procedure) {
            $html .= sprintf(
                '<a href="javascript:void(0)" class="booking-procedure" data-procedure="%d">%s</a><br>',
                $procedure['ID'],
                htmlspecialcharsbx($procedure['NAME'])
            );
        }

        $html .= self::getJsInit($elementId);
        return $html;
    }

    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        // Получаем ID элемента врача
        $elementId = 0;

        // Пробуем все возможные источники ID
        if (isset($strHTMLControlName['VALUE'])) {
            $elementId = $strHTMLControlName['VALUE'];
        } elseif (isset($value['ELEMENT_ID'])) {
            $elementId = $value['ELEMENT_ID'];
        } elseif (isset($value['VALUE']['ELEMENT_ID'])) {
            $elementId = $value['VALUE']['ELEMENT_ID'];
        } elseif (isset($_REQUEST['ID']) && empty($strHTMLControlName['MODE'])) {
            // Детальная страница
            $elementId = $_REQUEST['ID'];
        }

        if (!$elementId) {
            return '<span style="color: #999;">ID не определён</span>';
        }

        // Получаем процедуры врача
        $procedures = self::getDoctorProcedures($elementId);

        if (empty($procedures)) {
            return '<span style="color: #999;">Нет процедур</span>';
        }

        // Генерируем кликабельные ссылки
        $html = '<div style="max-height: 100px; overflow-y: auto;">';
        foreach ($procedures as $procedure) {
            $html .= sprintf(
                '<a href="javascript:void(0)" 
                class="booking-procedure" 
                data-procedure="%d" 
                data-doctor="%d"
                onclick="BX.Otus.BookingPopup.openPopup(%d, %d, \'%s\')">
                %s
            </a><br>',
                $procedure['ID'],
                $elementId,
                $elementId,
                $procedure['ID'],
                htmlspecialcharsbx($procedure['NAME']),
                htmlspecialcharsbx($procedure['NAME'])
            );
        }
        $html .= '</div>';

        // Инициализация JS (один раз на страницу)
        static $jsIncluded = false;
        if (!$jsIncluded) {
            $html .= self::getJsInitList();
            $jsIncluded = true;
        }

        return $html;
    }

    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return htmlspecialcharsbx($value['VALUE'] ?? '');
        //return self::GetPublicViewHTML($arProperty, $value, $strHTMLControlName);
    }

    public static function GetSearchContent($arProperty, $value, $strHTMLControlName)
    {
        return $value['VALUE'] ?? '';
    }

    private static function getDoctorProcedures($doctorId)
    {
        if (!$doctorId) return [];

        $procedures = [];
        $res = \CIBlockElement::GetProperty(
            16,
            $doctorId,
            [],
            ['CODE' => 'PROCEDURES_ID']
        );

        while ($prop = $res->Fetch()) {
            if ($prop['VALUE']) {
                $procedureRes = \CIBlockElement::GetByID($prop['VALUE']);
                if ($procedure = $procedureRes->Fetch()) {
                    $procedures[] = [
                        'ID' => $procedure['ID'],
                        'NAME' => $procedure['NAME']
                    ];
                }
            }
        }

        return $procedures;
    }

    private static function getJsInit($doctorId)
    {
        ob_start(); ?>
        <script>
            BX.ready(function() {
                BX.Otus.BookingPopup.init(<?= $doctorId ?>);
            });
        </script>
    <?php
        return ob_get_clean();
    }

    private static function getJsInitList()
    {
        ob_start(); ?>
        <script>
            BX.ready(function() {
                document.querySelectorAll('.booking-procedure').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        var doctorId = this.dataset.doctor;
                        var procedureId = this.dataset.procedure;
                        var procedureName = this.textContent;

                        if (window.BX && BX.Otus && BX.Otus.Booking) {
                            BX.Otus.Booking.openPopup(doctorId, procedureId, procedureName);
                        }
                    });
                });
            });
        </script>
<?php
        return ob_get_clean();
    }
}

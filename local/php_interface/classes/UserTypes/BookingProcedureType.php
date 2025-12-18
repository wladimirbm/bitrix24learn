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
        //return htmlspecialcharsbx($value['VALUE'] ?? '');
        return self::GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName);
    }
    
    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return self::GetPublicViewHTML($arProperty, $value, $strHTMLControlName);
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
}
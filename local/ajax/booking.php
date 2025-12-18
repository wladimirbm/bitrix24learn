<?
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!CModule::IncludeModule('iblock')) {
    echo json_encode(['success' => false, 'error' => 'Модуль инфоблоков не подключён']);
    exit;
}

function isTimeSlotAvailable($doctorId, $datetime, &$error = '')
{
    $datetimeBitrix = str_replace('T', ' ', $datetime) . ':00';
    $timestamp = MakeTimeStamp($datetimeBitrix);

    if (strtotime($datetimeBitrix) <= time()) {
        $error = 'Нельзя бронировать прошедшее время';
        return false;
    }

    // Наш интервал: от 30 минут до начала до 30 минут после начала
    $checkStart = date('Y-m-d H:i:s', $timestamp - 30 * 60);  // За 30 мин до
    $checkEnd = date('Y-m-d H:i:s', $timestamp + 30 * 60);    // Через 30 мин после

    // Конвертируем в формат Битрикс
    $checkStartBitrix = ConvertDateTime($checkStart, "DD.MM.YYYY HH:MI:SS");
    $checkEndBitrix = ConvertDateTime($checkEnd, "DD.MM.YYYY HH:MI:SS");

    // Ищем ВСЕ бронирования врача в этом интервале
    $res = CIBlockElement::GetList(
        ['PROPERTY_WRITETIME' => 'ASC'],
        [
            'IBLOCK_ID' => 21,
            'PROPERTY_DOCTOR' => $doctorId,
            '>=PROPERTY_WRITETIME' => $checkStartBitrix,
            '<=PROPERTY_WRITETIME' => $checkEndBitrix
        ],
        false,
        false,
        ['ID', 'PROPERTY_WRITETIME']
    );

    while ($booking = $res->Fetch()) {
        $bookingTimestamp = MakeTimeStamp($booking['PROPERTY_WRITETIME_VALUE']);
        $minutesDiff = abs($timestamp - $bookingTimestamp) / 60;

        if ($minutesDiff < 30) {
            // Нашли конфликтное бронирование
            $bookingTime = FormatDate('d.m.Y H:i', $bookingTimestamp);

            if ($bookingTimestamp < $timestamp) {
                $error = sprintf(
                    'Мало времени после предыдущего приёма %s (всего %.0f мин). Нужно 30 мин.',
                    $bookingTime,
                    $minutesDiff
                );
            } else {
                $error = sprintf(
                    'Время слишком близко к следующему приёму %s (всего %.0f мин). Нужно 30 мин.',
                    $bookingTime,
                    $minutesDiff
                );
            }
            return false;
        }
    }

    return true;
}

$result = ['success' => false];

if (check_bitrix_sessid()) {
    $doctorId = (int)$_POST['doctorId'];
    $procedureId = (int)$_POST['procedureId'];
    $patientName = trim($_POST['patientName']);
    $datetime = $_POST['datetime'];

    if (isTimeSlotAvailable($doctorId, $datetime, $errorMessage)) {
        $datetimeBitrix = str_replace('T', ' ', $datetime) . ':00';
        $el = new CIBlockElement;
        $res = $el->Add([
            'IBLOCK_ID' => 21, // IBLOCK_BOOKING_ID
            'NAME' => 'Бронирование #' . time(),
            'PROPERTY_VALUES' => [
                'FIO' => $patientName,
                'WRITETIME' => $datetimeBitrix,
                'DOCTOR' => $doctorId,
                'PROCEDURE' => $procedureId
            ]
        ]);

        if ($res) {
            $result['success'] = true;
        } else {
            $result['error'] = $el->LAST_ERROR;
        }
    } else {
        $result['error'] = $errorMessage;
    }
}

echo json_encode($result);

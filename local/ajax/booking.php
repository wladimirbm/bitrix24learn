<?
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!CModule::IncludeModule('iblock')) {
    echo json_encode(['success' => false, 'error' => 'Модуль инфоблоков не подключён']);
    exit;
}

function isTimeSlotAvailable($doctorId, $datetime, &$error = '')
{
    // Преобразуем к единому формату для сравнений
    $datetimeBitrix = str_replace('T', ' ', $datetime) . ':00';
    $timestamp = MakeTimeStamp($datetimeBitrix);

    if ($timestamp <= time()) {
        $error = 'Нельзя бронировать прошедшее время';
        return false;
    }

    // Теперь работаем с отформатированным временем
    $timeStart = date('Y-m-d H:i:s', $timestamp);
    $timeEnd = date('Y-m-d H:i:s', $timestamp + 30 * 60);


    // Проверка на занятость в указанный интервал
    $res = CIBlockElement::GetList(
        ['PROPERTY_WRITETIME' => 'ASC'],
        [
            'IBLOCK_ID' => 21, // IBLOCK_BOOKING_ID
            'PROPERTY_DOCTOR' => $doctorId,
            '>=PROPERTY_WRITETIME' => $timeStart,
            '<=PROPERTY_WRITETIME' => $timeEnd
        ],
        false,
        false,
        ['ID', 'PROPERTY_WRITETIME']
    );

    if ($booking = $res->Fetch()) {
        $bookingTime = FormatDate('H:i', MakeTimeStamp($booking['PROPERTY_WRITETIME_VALUE']));
        $error = sprintf('Время занято. У врача уже есть приём в %s. Выберите другое время.', $bookingTime);
        return false;
    }

    // Проверка интервала 30 минут после предыдущего приёма
    $timeBeforeStart = date('Y-m-d H:i:s', $timestamp - 30 * 60);
    $res = CIBlockElement::GetList(
        [],
        [
            'IBLOCK_ID' => 21,
            'PROPERTY_DOCTOR' => $doctorId,
            '>=PROPERTY_WRITETIME' => $timeBeforeStart,
            '<PROPERTY_WRITETIME' => $timeStart
        ],
        false,
        ['nTopCount' => 1],
        ['ID', 'PROPERTY_WRITETIME']
    );

    if ($previousBooking = $res->Fetch()) {
        $prevTime = MakeTimeStamp($previousBooking['PROPERTY_WRITETIME_VALUE']);
        $minutesBetween = ($timestamp - $prevTime) / 60;

        if ($minutesBetween < 30) {
            $error = sprintf('Мало времени после предыдущего приёма (осталось %.0f минут). Выберите время на 30 минут позже.', $minutesBetween);
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

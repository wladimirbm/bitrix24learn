<?php
namespace Otus\Crmcustomtab\Crm;

use Otus\Crmcustomtab\Orm\DoctorsTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
class Handlers
{
    public static function updateTabs(Event $event): EventResult
    {
        $titletodisplaytab = Option::get('otus.crmcustomtab', 'TITLE_TO_DISPLAY_TAB');
        $availableEntityIds = Option::get('otus.crmcustomtab', 'ENTITIES_TO_DISPLAY_TAB');
        $availableEntityIds = explode(',', $availableEntityIds);
        $entityTypeId = $event->getParameter('entityTypeID');
        $entityId = $event->getParameter('entityID');
        $tabs = $event->getParameter('tabs');
        if (in_array($entityTypeId, $availableEntityIds)) {
            $tabs[] = [
                'id' => 'doctor_tab_' . $entityTypeId . '_' . $entityId,
                'name' => trim($titletodisplaytab) ?? Loc::getMessage('OTUS_CRMCUSTOMTAB_TAB_TITLE'),
                'enabled' => true,
                'loader' => [
                    'serviceUrl' => sprintf(
                        '/bitrix/components/otus.crmcustomtab/doctor.grid/lazyload.ajax.php?site=%s&%s',
                        \SITE_ID,
                        \bitrix_sessid_get()
                    ),
                    'componentData' => [
                        'template' => '',
                        'params' => [
                            'ORM' => DoctorsTable::class,
                            'DEAL_ID' => $entityId,
                        ],
                    ],
                ],
            ];
        }

        return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs,]);
    }
}

<?php
class PluginSubtaskgeneratorContainer extends CommonDBTM
{

  public static $rightname = 'config';
  public static function canCreate()
  {
    return self::canUpdate();
  }

  public static function canPurge()
  {
    return self::canUpdate();
  }
  public static function getTypeName($nb = 0)
  {
    return __('Основная', 'subtaskgenerator');
  }
  public function rawSearchOptions()
  {
    $tab = [];

    $tab[] = [
      'id'            => 1,
      'table'         => self::getTable(),
      'field'         => 'name',
      'name'          => __('Name'),
      'datatype'      => 'itemlink',
      'itemlink_type' => self::getType(),
      'massiveaction' => false,
    ];

    $tab[] = [
      'id'            => 2,
      'table'         => self::getTable(),
      'field'         => 'label',
      'name'          => __('Label'),
      'massiveaction' => false,
      'autocomplete'  => true,
    ];

    $tab[] = [
      'id'         => 5,
      'table'      => self::getTable(),
      'field'      => 'is_active',
      'name'       => __('Active'),
      'datatype'   => 'bool',
      'searchtype' => ['equals', 'notequals'],
    ];

    $tab[] = [
      'id'            => 6,
      'table'         => 'glpi_entities',
      'field'         => 'completename',
      'name'          => __('Entity'),
      'massiveaction' => false,
      'datatype'      => 'dropdown',
    ];

    $tab[] = [
      'id'            => 7,
      'table'         => self::getTable(),
      'field'         => 'is_recursive',
      'name'          => __('Child entities'),
      'massiveaction' => false,
      'datatype'      => 'bool',
    ];

    $tab[] = [
      'id'            => 8,
      'table'         => self::getTable(),
      'field'         => 'id',
      'name'          => __('ID'),
      'datatype'      => 'number',
      'massiveaction' => false,
    ];

    return $tab;
  }

  public function showForm($ID, $options = [])
  {
    $this->initForm($ID, $options);
    $this->showFormHeader($options);
    $rand = mt_rand();
    echo '<tr>';
    echo "<td width='20%'>" . __('Name') . ' : </td>';
    echo "<td width='30%'>";
    echo Html::input(
      'name',
      [
        'value' => $this->fields['name'],
      ],
    );
    echo '</td>';
    echo "<td width='20%'>&nbsp;</td>";
    echo "<td width='30%'>&nbsp;</td>";
    echo '</tr>';

    echo '<tr>';
    echo '<td>' . __('Home') .' '. __('Category') . ' : </td>';
    echo '<td>';
    $excluded_itilcategories = [];
    $glpi_plugin_subtaskgenerator_containers = new self();
    $cat = $glpi_plugin_subtaskgenerator_containers->find();
    foreach ($cat as $c) {
      $excluded_itilcategories[] = $c['itilcategory_id'];
    }
    $glpi_plugin_subtaskgenerator_itilcategories = new PluginSubtaskgeneratorItilcategory();
    $cat = $glpi_plugin_subtaskgenerator_itilcategories->find();
    foreach ($cat as $c) {
      $excluded_itilcategories[] = $c['itilcategory_id'];
    }
    ITILCategory::dropdown([
      'name'       => 'itilcategory_id',
      'value'      => $this->fields['itilcategory_id'],
      'entity'     => $_SESSION['glpiactive_entity'],
      'right'      => 'all',
      'used' => $excluded_itilcategories //исключаем уже выбранных пользователей из выпадающего списка выбора пользователей
    ]);
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>' . __('Active') . ' : </td>';
    echo '<td>';
    Dropdown::showYesNo('is_active', $this->fields['is_active']);
    echo '</td>';
    echo '</tr>';
    $this->showFormButtons($options);

    return true;
  }

  public function defineTabs($options = [])
  {
    $ong = [];
    $this->addDefaultFormTab($ong);
    $this->addStandardTab('PluginSubtaskgeneratorItilcategory', $ong, $options);
    $this->addStandardTab('PluginSubtaskgeneratorTicket', $ong, $options);
    return $ong;
  }
  public static function itemAdd(CommonDBTM $item)
  {
    global $DB, $CFG_GLPI;
    //bafore save change priority
    // Создаём экземпляр текущего класса
    //  die(json_encode($item->input));
    $container = new self();
    if($container = current($container->find(['itilcategory_id'=>$item->fields['itilcategories_id']])))
    {
      $ticket = new PluginSubtaskgeneratorTicket();
      $data = [
        'container_id' => $container['id'],
        'ticket_id' => $item->fields['id'],
      ];
      if($ticket->add($data))
      {
        //add log
        $PluginSubtaskgeneratorItilcategory = new PluginSubtaskgeneratorItilcategory();

        foreach ($PluginSubtaskgeneratorItilcategory->find(['container_id' => $container['id']]) as $value) {
          $subTicket = new Ticket();
          $data = [        // Название новой заявки
            'content'            => $value['description'],     // Описание
            'itilcategories_id'  => $value['itilcategory_id'],                         // Категория заявки
            'entities_id'        => $_SESSION['glpiactive_entity'], // Текущая сущность
            'users_id_recipient' => $value['requester_id'], // Пользователь, создавший заявку
            '_users_id_requester' => [
              '_actors_2'  => $value['requester_id'],
            ], // Пользователь, создавший заявку
            'users_id_recipient' => $value['requester_id'], // Пользователь, создавший заявку
            'users_id_lastupdater' => $value['requester_id'], // Пользователь, создавший заявку
            'type'               => Ticket::DEMAND_TYPE        // Тип заявки обращение
          ];
          if($subTicket->add($data))
          {
            //add log
            // ID новой заявки
            $newTicketId = $subTicket->fields['id'];

            // ID родительской заявки
            $parentTicketId = $item->fields['id']; // Укажите ID родительской заявки

            // Привязываем новую заявку к родительской
            $childLink = new Ticket_Ticket();
            $linkData = [
              'tickets_id_1' => $parentTicketId, // ID родительской заявки
              'tickets_id_2' => $newTicketId,   // ID дочерней заявки
              'link'         => Ticket_Ticket::SON_OF  // Тип связи
            ];
            if($value['slas_id'])
            {
              $subTicket->update([
                'id' => $newTicketId,
                'slas_id_ttr'=>$value['slas_id'],
                'users_id_lastupdater' => $value['requester_id'],
              ]);
            }
            if($childLink->add($linkData))
            {
              //add log
            }
          }
        }
      }
    }

    return;
  }
}

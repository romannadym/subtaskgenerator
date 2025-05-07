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
      'used' => $excluded_itilcategories //исключаем уже выбранные категории из выпадающего списка выбора пользователей
    ]);
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo "<td width='20%'>" . __('Assigned') . ' : </td>';
    echo '<td>';
    User::dropdown([
        'name'       => 'assign_id',
        'value'      => $this->fields['assign_id'] ?? 0,
        'entity'     => $_SESSION['glpiactive_entity'],
        'right'      => 'all',
        //'used' => $excluded_users //исключаем уже выбранных пользователей из выпадающего списка выбора пользователей
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

  public static function preItemAddTicketSubtaskgenerator(CommonDBTM $item)
  {
    global $DB, $CFG_GLPI;
    $container = new self();
    //Если есть правило по родительской категории
    if($container = current($container->find(['itilcategory_id'=>$item->input['itilcategories_id']])))
    {
      //Если правило активно назначем исполниетля системного пользователя для родиетльской заявки
      if($container['is_active'] && $container['assign_id'])
      {
        $assign = [
          "itemtype"=> "User",
          "items_id"=> $container['assign_id'],
          "use_notification"=> 0,
          "alternative_email"=> "",
          "default_email"=> ""
        ];
       $item->input['_actors']['assign'] = [$assign];
      }
    }
  }

  public static function itemAdd(CommonDBTM $item)
  {
    global $DB, $CFG_GLPI;
    //bafore save change priority
    // Создаём экземпляр текущего класса
    //die(json_encode($item));

    $container = new self();
    //Если есть правило по родительской категории
    if($container = current($container->find(['itilcategory_id'=>$item->fields['itilcategories_id']])))
    {
      if(!$container['is_active'])
      {
        return; //Если правило не активно пропускаем
      }
      $ticket = new PluginSubtaskgeneratorTicket();
      $data = [
        'container_id' => $container['id'],
        'ticket_id' => $item->fields['id'],
      ];
      if($ticket->add($data))
      {
        //add log
        $PluginSubtaskgeneratorItilcategory = new PluginSubtaskgeneratorItilcategory();
        $ticketInfo = $PluginSubtaskgeneratorItilcategory->find(['container_id' => $container['id']]);
        ksort($ticketInfo);//Сортировка по ключам
        $ticketInfo = current($ticketInfo);
      //  file_put_contents(GLPI_ROOT.'/tmp/buffer.txt',PHP_EOL.PHP_EOL."[".date("Y-m-d H:i:s")."] ". json_encode($PluginSubtaskgeneratorItilcategory->find(['container_id' => $container['id'],['ORDER' => 'id ASC']]),JSON_UNESCAPED_UNICODE), FILE_APPEND);
          if (!$ticketInfo['requester_id'])
          {
            $ticketInfo['requester_id'] = Session::getLoginUserID(); //Если автор создания подзадачи не указан то подставляем того кто создает основную задачу
          }
          $subTicket = new Ticket();
          $data = [        // Название новой заявки
            'content'            => $ticketInfo['description'],     // Описание
            'itilcategories_id'  => $ticketInfo['itilcategory_id'],                         // Категория заявки
            'entities_id'        => $_SESSION['glpiactive_entity'], // Текущая сущность
            'users_id_recipient' => $ticketInfo['requester_id'], // Пользователь, создавший заявку
            '_users_id_requester' => [
              '_actors_2'  => $ticketInfo['requester_id'],
            ], // Пользователь, создавший заявку
            'users_id_recipient' => $ticketInfo['requester_id'], // Пользователь, создавший заявку
            'users_id_lastupdater' => $ticketInfo['requester_id'], // Пользователь, создавший заявку
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
              'link'         => Ticket_Ticket::PARENT_OF  // Тип связи
            ];
            if($ticketInfo['slas_id'])
            {
              $subTicket->update([
                'id' => $newTicketId,
                'slas_id_ttr'=>$ticketInfo['slas_id'],
                'users_id_lastupdater' => $ticketInfo['requester_id'],
              ]);
            }
            if($childLink->add($linkData))
            {
              //add log
            }
          }

      }
    }

    return;
  }

  public static function itemUpdate(CommonDBTM $item)
  {
    $ticket_id = $item->fields['id'];

    $ticket_ticket = new Ticket_Ticket();
    $parents = $ticket_ticket->getLinkedTicketsTo($ticket_id, Ticket_Ticket::PARENT_OF);//находим связанные задачи с типом связи - 3

    foreach($parents as $parent)
    {

      if(isset($parent['tickets_id'])) //еслис в связи есть есть id родителя заявки
      {

        $ticket_parent_plugin = new PluginSubtaskgeneratorTicket();
        //если родительская заявка  связана с плагином то останавливаем
        if(!$ticket_parent_plugin = current($ticket_parent_plugin->find(['ticket_id' => $parent['tickets_id']])))
        {
          return;
        }

        $chaild_ticket_plan = new PluginSubtaskgeneratorItilcategory();
        //если в правилах нет критериев создания потомков то останавливаем
        if(!$chaild_ticket_plan = $chaild_ticket_plan->find(['container_id' => $ticket_parent_plugin['container_id']]))
        {
          return;
        }
        ksort($chaild_ticket_plan);//сортируем по ключу
        //получаем всех потомков родительской заявки
        $childs_tickets = new Ticket_Ticket();

        $childs_tickets = $childs_tickets->getLinkedTicketsTo($parent['tickets_id'], Ticket_Ticket::SON_OF);
      //  file_put_contents(GLPI_ROOT.'/tmp/buffer.txt',PHP_EOL.PHP_EOL."[".date("Y-m-d H:i:s")."] ". json_encode($childs_tickets,JSON_UNESCAPED_UNICODE), FILE_APPEND);
        ksort($childs_tickets);//сортируем по ключу
        foreach($childs_tickets as $key => $child)
        {
          //если у родителя есть родительская связь то удаляем эту связь из массива
          if(!isset($child['tickets_id_1']))
          {
            unset($childs_tickets[$key]);
            continue;
          }
          //агрегируем потомков
          $ticket = new Ticket();
          $ticket = current($ticket->find(['id' => $child['tickets_id']]));
          if($ticket)
          {
            $childs_tickets[$key]['itilcategories_id'] = $ticket['itilcategories_id'];
            $childs_tickets[$key]['status'] = $ticket['status'];
          }
        }
        //если не все потомки созданы то создаем последовательно в зависимотси от статуса
        if(count($childs_tickets) < count($chaild_ticket_plan))
        {

          if($last_child_ticket = end($childs_tickets)['status'] == 5)
            {
              array_splice($chaild_ticket_plan, 0, count($childs_tickets));
              $plan = current($chaild_ticket_plan);
              self::createSubTicket($plan, $parent['tickets_id']);
              return;

            }
        }

        //если все птоомки созданы и выполнены то работем с родительской заявкой
        $status = false;

        if(count($childs_tickets) == count($chaild_ticket_plan))
        {

          $count_status_true = [];
          foreach($childs_tickets as $child)
          {
            if($child['status'] == 5)
            {
              $count_status_true[] = $child['status'];
            }
          }
        //  die(json_encode($childs_tickets));
          if(count($count_status_true) == count($chaild_ticket_plan))
          {
            $status = true;
          }
        }

        $parentTicket = new Ticket();
        if($status)
        {
          $parentTicket->update([
            'id' => $parent['tickets_id'],
            'status'=>5
          ]);
        }
        else
        {
          $parentTicket->update([
            'id' => $parent['tickets_id'],
            'status'=>3
          ]);
        }

      //  die(json_encode($ticket));

      // die(json_encode($childs_tickets));

      }

    }

  }

  public static function createSubTicket($plan, $parent_id)
  {
    if(!$plan || !$parent_id)
    {
      return;
    }
    //Если инициатор не указан то подставляем инициатора из  родительского обращения
    if(empty($plan['requester_id']))
    {
      $ticket_user = new Ticket_User();
      $initiators = $ticket_user->find([
          'tickets_id' => $parent_id,
          'type'       => Ticket_User::REQUESTER
      ]);
      $plan['requester_id'] = current($initiators)['users_id'];
    }
    $subTicket = new Ticket();
    $data = [        // Название новой заявки
      'content'            => $plan['description'],     // Описание
      'itilcategories_id'  => $plan['itilcategory_id'],                         // Категория заявки
      'entities_id'        => $_SESSION['glpiactive_entity'], // Текущая сущность
      'users_id_recipient' => $plan['requester_id'], // Пользователь, создавший заявку
      '_users_id_requester' => [
        '_actors_2'  => $plan['requester_id'],
      ], // Пользователь, создавший заявку
      'users_id_recipient' => $plan['requester_id'], // Пользователь, создавший заявку
      'users_id_lastupdater' => $plan['requester_id'], // Пользователь, создавший заявку
      'type'               => Ticket::DEMAND_TYPE        // Тип заявки обращение
    ];

    if($subTicket->add($data))
    {
      //add log
      // ID новой заявки
      $newTicketId = $subTicket->fields['id'];

      // Привязываем новую заявку к родительской
      $childLink = new Ticket_Ticket();
      $linkData = [
        'tickets_id_1' => $parent_id, // ID родительской заявки
        'tickets_id_2' => $newTicketId,   // ID дочерней заявки
        'link'         => Ticket_Ticket::PARENT_OF  // Тип связи
      ];
      if($plan['slas_id'])
      {
        $subTicket->update([
          'id' => $newTicketId,
          'slas_id_ttr'=>$plan['slas_id'],
          'users_id_lastupdater' => $plan['requester_id'],
        ]);
      }
      if($childLink->add($linkData))
      {
        //add log
      }
      return;
    }
  }
}

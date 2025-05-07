<?php
class PluginSubtaskgeneratorFields extends CommonDBChild
{

  static function showForTab  ($params)
  {
    // Проверяем, что это форма заявки
    if ($params['item']::getType() != 'Ticket') {
        return false;
    }
    //if ticket edit page
    if (strpos($_SERVER['REQUEST_URI'], "ticket.form.php") !== false && isset($_GET['id']))
    {

        $parentTickets = new PluginSubtaskgeneratorTicket();

        // file_put_contents(GLPI_ROOT.'/tmp/buffer.txt',PHP_EOL.PHP_EOL. json_encode($params,JSON_UNESCAPED_UNICODE), FILE_APPEND);
        //проверяем есть создана ли родительская задача с участием плагина subgenerator
         if($parentTicket = current($parentTickets->find(['ticket_id' => $_GET['id']])))
         {
           $container = new PluginSubtaskgeneratorContainer();
           //Проверяем есть ли правлио
           if($container = current($container->find(['id'=>$parentTicket['container_id']])))
           {
             //Проверяем активно ли правило
             if($container['is_active'])
             {
               //изменяем поля заявки
               echo Html::scriptBlock(<<<JAVASCRIPT
                 $(document).ready(function(){
                  $('select[name=status]').attr('disabled',true);//Блокироем изменение статуса пользователем
                  $('select[data-actor-type=assign]').attr('disabled',true); //Блокируем назанчение специалиста пользователем
                  $('select[data-actor-type=assign]').parent().find('button').remove();//Убираем кнопку назанчить себя исполнителем
                  $('button[form^=linked_tickets]').hide();
                  var textAlert = `<div class="alert bg-white alert-dismissible m-2" style="color: var(--tblr-pink) !important; border-left-color: rgba(247, 103, 7, 0.6);">
                                    <div class="d-flex">
                                        <i class="ti ti-alert-triangle fa-2x me-2"></i>
                                        <div class="overflow-hidden">
                                            <h3 class="mt-1">Внимание!!!</h3>
                                          <div class="plugin_news_alert-content overflow-hidden">
                                              <p>Данное обращение закроется автоматически после того как будут выполнены все обращения-потомки.</p>
                                          </div>
                                        </div>
                                    </div>
                                 </div>`;
                    //$('#heading-main-item').before(textAlert); //выводем сообщение
                 })

                 JAVASCRIPT
               );
             }
           }

         }

    }
    return false;
  }

}

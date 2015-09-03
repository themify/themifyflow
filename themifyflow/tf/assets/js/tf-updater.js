(function($){
    'use strict';
    $(document).ready(function(){
       $('.tf_updater_activation_btn').click(function(e){
           e.preventDefault();
           var $licence = $.trim($(this).closest('tr').find('input[type="text"]').val());
           if(!$licence){
               return false;
           }
           var $self = $(this);
           var form = $self.closest('form');
           var $key = $self.data('key');
           var $data = {'key':$key};
            var $hidden = form.find('input[type="hidden"],input[type="text"]');
            $hidden.each(function(){
                $data[$(this).attr('name')] = $(this).val();
            });
            $data['action'] = 'tf_updater_activate_license';
           $.ajax({
              url: ajaxurl,
              data:$data,
              dataType:'json',
              type:'POST',
              beforeSend:function(){
                form.addClass('tf_updater_wait');
              },
              complete:function(){
                form.removeClass('tf_updater_wait');
              },
              success:function($resp){
                  if($resp){
                      var $tr = $self.closest('tr');
                      var $expires =  $tr.find('.tf_date_expires');
                      var $error =  $tr.find('.tf_updater_notifaction');
                      var $auto = $tr.find('input[type="checkbox"]');
                      if($resp.error){
                          $error.addClass('tf_updater_error').children('p').html($resp.error)
                          $expires.html('');
                      }
                      else{
                          $expires.html($resp.expires);
                          $error.removeClass('tf_updater_error updated').children('p').html('');
                      }
                    if($resp.auto || $resp.status==='valid'){
                        $auto.removeAttr('disabled');
                    }
                    else if($resp.error || $resp.status==='invalid'){
                         $auto.attr({'disabled':'disabled','checked':false});
                    }
                    $self.val($resp.btn);
                  }
              }
           });
           
       });
       $('.tf_updater_auto_update').change(function(e){
            var $self = $(this);
            var form = $self.closest('form');
            var $active =$self.is(':checked')?1:0;
           
            var $data = {'akey':$self.attr('name'),'value':$active};
            var $hidden = form.find('input[type="hidden"]');
            $hidden.each(function(){
                $data[$(this).attr('name')] = $(this).val();
            });
            $data['action'] = 'tf_updater_activate_auto';
            $.ajax({
                url:ajaxurl,
                data:$data,
                type:'POST',
                dataType:'json',
                beforeSend:function(){
                    form.addClass('tf_updater_wait');
                },
                complete:function(){
                    form.removeClass('tf_updater_wait');
                },
                success:function($resp){
                    if($resp){
                      var $error =  $self.closest('tr').find('.tf_updater_notifaction');
                      var $msg = $error.children('p');
                      if(!$resp.success){
                          $error.removeClass('updated').addClass('tf_updater_error');
                      }
                      else{
                          $error.removeClass('tf_updater_error').addClass('updated');
                      }
                      $msg.html($resp.msg);
                    }
                }
            });
          
        });
    });    
    
})(jQuery);
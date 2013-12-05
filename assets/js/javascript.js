/* 
    jQuery file for whitemice social feed plugin 

*/

(function($){

    $(document).ready( function($) {
        $('label.hasTip').tooltip();

        $('#wall-menu-tabs li').on( 'click', function(event){
            var tab = $(this).attr('id').replace('tab-','');

            $('#wall-menu-tabs li.active').removeClass('active');
            $(this).addClass('active');
            window.location.hash = tab;
            $('.tab-content').each(function(){
                $(this).addClass('hide');
            });
            $('#main-tab-'+tab).removeClass('hide');
        });

        if(window.location.hash =='') {
            window.location.hash='general';
        }

        //set delete button
        $('.feed-user li a').on( 'click', function(){
            var ptype = $(this).parent().parent().attr('id').split('_')[0];
            if( confirm('Are you sure remove `'+jQuery('#'+ptype+'_item_remove').val()+'`' ) ) {
                 document.forms['remove_'+ptype+'_item'].submit();         
            }
            return false;

        }); 
        //set add button
        $('.feed-user .button').on( 'click', function(){
            var ptype = $(this).parent().find('#create_type').val();
            var txtprofile = prompt('Please enter profile name:');
            if(txtprofile!=''){
                $('#'+ptype+'_user').val(txtprofile);
                return create_item(document.forms[ptype+'_create_item']);
            }
            return false;

        });

        $('.dwsf-verify-feed').on( 'click', function(event){
            var t = $(this),
                type = t.data('key'),
                url = '',
                query = $('#'+type+'_item_query').val();
                if( query ){
                    switch(type){
                        case 'facebook':
                            t.find('.spinner').show();
                            $.ajax({
                                url:'http://graph.facebook.com/'+query,
                                async: false,
                                dataType: 'json',
                                success: function(response){
                                    t.find('.spinner').hide();
                                    if( response.is_published == true ) {
                                        url = 'https://www.facebook.com/feeds/page.php?id='+response.id+'&format=rss20';
                                    } else {
                                        alert('Please provide a Facebook page');
                                    }
                                }
                            });
                            break;
                        case 'twitter':
                            url = 'https://twitter.com/#!/search/'+encodeURIComponent(query);
                            break;
                        case 'youtube':
                            url = 'http://gdata.youtube.com/feeds/base/users/'+query+'/uploads?orderby=updated&alt=rss';
                            break;
                        case 'instagram':
                            if( query.toUpperCase() == '[POPULAR]' ){
                                url = "http://web.stagram.com/rss/popular/";
                            } else if ( query.substring(0,1) == '@') {
                                username = query.substring(1);
                                url = "http://widget.stagram.com/rss/n/"+username+"/";
                            } else if ( query.substring(0,1) == '#') {
                                keyword = query.substring(1);
                                url = ("http://widget.stagram.com/rss/tag/"+keyword+"/");
                            } else {
                                url = ("http://widget.stagram.com/rss/tag/"+query+"/");
                            }
                            break;
                        case 'flickr':
                            url = "http://api.flickr.com/services/feeds/photos_public.gne?format=rss2&id="+query;
                            break;
                        case 'custom':
                            url = query;
                            break;
                        case 'vimeo':
                            var vtype = $('#vimeo_item_vtype option:selected').val();
                            switch(vtype){
                                case 'album':
                                    url = "http://vimeo.com/api/v2/album/"+query+"/videos.xml";
                                    break;
                                case 'group':
                                    url = "http://vimeo.com/api/v2/group/"+query+"/videos.xml";
                                    break;
                                case 'channel':
                                    url = "http://vimeo.com/api/v2/channel/"+query+"/videos.xml";
                                    break;
                                default:
                                    url = "http://vimeo.com/api/v2/"+query+"/all_videos.xml";
                                    break;
                            }
                            break;
                    }
                    if( url ){
                        window.open(url, '_blank');
                    }else{
                        return false;
                    }
            }
        } );

        $('#dwsf_use_custom_time').on('click', function(event){
            if ( $(this).prop('checked')  ){
                $('.custom_cron_time_box').slideDown();
                $('.custom_cron_time').removeAttr('disabled');
            } else{
                jQuery('.custom_cron_time_box').hide();
                $('.custom_cron_time').attr('disabled', 'disabled');
            }
        });

        $('.custom_cron_time_select').on('change',function(event) {
            var input_field = $(this).data('storage'),
                time = $(this).val();

            if( time != -1 ){
                $( '#'+input_field ).val( time );
                $( '#'+input_field ).addClass('hide');
            }else{
                $( '#'+input_field ).removeClass('hide');
                $( '#'+input_field ).val( '' );
                $( '#'+input_field ).focus();
            }
        });

        $('.dwsf-profile-tab').on('click', function(event){
            event.preventDefault();
            var t = $(this),
                data = t.data('query'),
                type = t.data('key'),
                string_hash = window.location.hash,
                array_hash = string_hash.split('|');
            //Change hash on url
            window.location.hash = array_hash[0] + '|' + data;

            if($('#'+type+'_items .'+data).hasClass('active')) return;

            $('#'+type+'_items').find('.active').removeClass('active').end()
                        .find('.'+data).addClass('active');

            //Item remove form
            $('#'+type+'_item_remove').val( data );
            $('#'+type+'-edit_title').text( data );
            var tab_options = t.closest('.tab-content').find('.feed-user-options');
            var loading_screen = $('<div class="loading" />').css({
                'height'             : tab_options.height(),
                'width'             : tab_options.width()
            });
            tab_options.css('opacity','.3').before( loading_screen );
            $.ajax({
                url: dwsf.ajaxUrl,
                type: 'GET',
                dataType: 'json',
                data: {
                    action: 'dwsf-get-profile',
                    type: type,
                    select_item: data
                },
                success: function(response, textStatus, xhr) {
                    tab_options.css('opacity','1');
                    loading_screen.remove();
                    if( response.success ){
                        $('#'+type+'_item_id').val(data);

                        //Change run-now link for each profile
                        var regex = /id=.*/i;
                        var link = $('#'+type+'_run_cron_for_each').attr('href');
                        var newlink = link.replace(link.match(regex),'id='+data );
                        $('#'+type+'_run_cron_for_each').attr('href',newlink);

                        //Fill Form
                        var item_prefix = '#'+type+'_item_';
                        if( 'vimeo' == type ) {
                            $('#vimeo_item_vtype').val(response.data.item['vtype']);
                        }

                        $( item_prefix + 'category' ).val(response.data.item['category']);

                        $( item_prefix + 'query' ).val(response.data.item['query']);

                        $( item_prefix + 'source_text').val(response.data.item['source_text']);
                        
                        $( item_prefix + 'limit').val( response.data.item['limit']);

                        $( item_prefix + 'useimage').val(response.data.item['use_image']);

                        $( item_prefix + 'update_status').val(response.data.item['update_post']);

                        $( item_prefix + 'postauthor').val(response.data.item['author']);

                        $( item_prefix + 'posttype').val(response.data.item['posttype']);

                        $( item_prefix + 'status').val(response.data.item['status']);

                        if( 'youtube' == type || 'vimeo' == type ){

                            $( item_prefix + 'video_embed_width').val(response.data.item['video_embed_width']);
                            $( item_prefix + 'video_embed_height').val(response.data.item['video_embed_height']);

                            $('input:radio[name='+type+'_item_video_loop]:checked').removeAttr('checked');
                            $('input:radio[name='+type+'_item_video_loop]').each(function(){
                                if( $(this).val() == response.data.item['video_loop'] ){
                                    $(this).attr('checked','checked');
                                }
                            });
                            $('input:radio[name='+type+'_item_video_autoplay]:checked').removeAttr('checked');
                            $('input:radio[name='+type+'_item_video_autoplay]').each(function(){
                                if( $(this).val() == response.data.item['video_autoplay'] ){
                                    $(this).attr('checked','checked');
                                }
                            });
                        }

                        if( type == 'facebook' || type == 'twitter' ) {
                            $( item_prefix + 'image_width_limit').val( response.data.item['width_image_limit'] );
                            $( item_prefix + 'image_height_limit').val( response.data.item['height_image_limit'] );
                        }
                        if( type == 'twitter' ){
                            $( item_prefix + 'retweet').val(response.data.item['retweet']);
                        }
                    }
                }
            });
        });
    });

    setInterval(function(){
         var hashString = new String(window.location.hash);
         hashString = hashString.substring(1);
         var array_hash = hashString.split('|');

         var activetab = array_hash[0];
         if($('#tab-'+activetab).hasClass('active')|| activetab=="") return;
         $('#tab-'+activetab).click();

         var activeProfile = array_hash[1];
         if( $('#'+activetab+'_items li.'+activeProfile).hasClass('active') || activeProfile == ''  ) return;
         $('#'+activetab+'_items li.'+activeProfile).click();

    },100);


    function create_item(obj){
        var type = jQuery(obj).find('#create_type').val();
        var vmtype= jQuery(obj).find('#vimeo_item_vtype_add').val();
        var username = jQuery(obj).find('#'+type+'_user').val();
        var wp_nonce = jQuery(obj).find('#'+type+'_create_item_wpnonce').val();

        if( username ){
            jQuery.post(
                dwsf.ajaxUrl,
                {
                    action: 'wall_social_feed_create_item',
                    type: type,
                    vtype:vmtype,
                    wp_nonce: wp_nonce,
                    username: username,
                },function(response){
                    if( response.status == 'error' ){
                        alert(response.msg);
                    }else{
                        jQuery('#'+type+'_items').append('<li onclick="jQuery.item_view(\''+response.msg+'\',\''+type+'\')">'+response.msg+'</li>');
                        jQuery(obj).find('#'+type+'_user').val('');
                        window.location.href = dwsf.optionPage+'&wm='+Math.random()+'#'+type;
                    }
                },'json'
            );
        }
        return false;
    }

    
})(jQuery);


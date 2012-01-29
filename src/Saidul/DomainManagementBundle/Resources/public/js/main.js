var Site = {
    init: function(){
        Site.bindEventListners();

    },

    bindEventListners: function(){

        Site.hideNonLocalDomain();
        $(this).data('displayed',false);
        $('#toggleNonlocalBtn').click(function(){
            if($(this).data('displayed')){
                Site.hideNonLocalDomain();
                $(this).data('displayed',false);
            }
            else{
                Site.showNonLocalDomain();
                $(this).data('displayed',true);
            }
        });


        $('#addRecordBtn').click(function(e){
            //alert(e);
            $('#addRecordMiniForm').slideToggle('fast');
            //e.stopEvent();
            return false;
        })
    },

    hideNonLocalDomain: function(){
        $(".column-host").each(function(){
             if(Site.isLocalDomain($(this).text())) return true;
             else $(this).parents("li").slideUp('fast');
        });
    },

    showNonLocalDomain: function(){
        $(".column-host").each(function(){
             if(Site.isLocalDomain($(this).text())) return true;
             else $(this).parents("li").slideDown('fast');
        });
    },

    isLocalDomain: function(domain){
        return /[\w\-\.]+.localhost.com/i.test(domain);
    }

}

$(document).ready(function(){
    Site.init();
})
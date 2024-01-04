define([
    'jquery'
], function($){
    return function(){
        $.widget('mage.dropdownDialog', $.mage.dropdownDialog, {
            open:function(){
                var $cartLink = $('div.minicart-wrapper a.showcart');
                
                window.location.href = $cartLink.attr("href");
            }
        });
        return $.mage.dropdownDialog;
    };
});

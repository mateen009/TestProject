require(['jquery'], function ($) {
    $(document).ready(function () {
        $("#chosen").change(function () {
            if ($('#chosen option:selected').val() == 0) {
                $('.rental-delivery-ship').css('display', 'block');
                $('.rental-delivery-local').css('display', 'none');
            } else {
                $('.rental-delivery-local').css('display', 'block');
                $('.rental-delivery-ship').css('display', 'none');
            }
        });
    });
});

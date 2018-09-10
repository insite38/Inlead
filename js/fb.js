jQuery(document).ready(function () {

    jQuery('.form-downSend').on('submit', function (event) {
        event.preventDefault();
        ObjectPlazaJs.callbackResponsFb(jQuery(this), '.form-downSend');
    });

    jQuery('.form-topSend').on('submit', function (event) {
        event.preventDefault();
        ObjectPlazaJs.callbackResponsFb(jQuery(this), '.form-topSend');
    });

    jQuery('.form-business-plaza').on('submit', function (event) {
        event.preventDefault();
        ObjectPlazaJs.callbackResponsFb(jQuery(this), '.form-business-plaza');
    });
});

ObjectPlazaJs = {

    callbackResponsFb: function (that, nameForm) {

        var formData = that.serialize();
        var action = that.attr('action');
        var errorClass = nameForm + ' .response_error';
        var okClass = nameForm + ' .response_ok';

        jQuery(errorClass).html('');
        jQuery(okClass).html('');

        this.ajaxResponseFb(formData, action, okClass, function (response) {

            if (response.error) {

                jQuery(errorClass).html(response.errors).css("color", "red");

            } else {

                jQuery(okClass).html(response.ok).css("color", "green");
                jQuery('.gugleCaptcha').hide();
            }
        });
    },

    ajaxResponseFb: function (data, action, okClass, callback) {

        jQuery.ajax({

            url: action,
            type: 'POST',
            data: data,
            dataType: 'json',

            beforeSend: function(okClass){

                $(okClass).html('<img src="/images/bx_loader.gif">');
            },

            success: function (response) {

                $('.response_ok').html('');
                console.log('ok-fb');
                console.log(response);
                callback(response);
            },

            error: function (response) {

                console.log('bad-fb');
                console.log(response);
            }
        });
    }
};


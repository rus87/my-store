$(document).ready(function(){



});

function send_form(){

    var form_data = $('#checkout_form').serialize();
    alert();

     $.ajax({
         type: 'POST',
         url: Routing.generate('app_checkout_checkform'),
         data: form_data,
         success: function(response)
         {
             //alert(response);
             //console.log(JSON.parse(response));
             if(response != 'OK :)')
             {
                 var errors = JSON.parse(response);
                 console.log(errors);

                 $('#email-error').html(null);
                 if(errors.form.children.email.errors)
                     for(var i=0; i<errors.form.children.email.errors.length; i++)
                         $('#email-error').append('<p>'+errors.form.children.email.errors[i]+'</p>');


                 var shippingErrors = errors.form.children.shipping.children;
                 console.log(shippingErrors);

                 $('#shipping-city-error').html(null);
                 if(shippingErrors.city.errors)
                     for(i=0; i<shippingErrors.city.errors.length; i++)
                         $('#shipping-city-error').append('<p>'+shippingErrors.city.errors[i]+'</p>');


                 $('#shipping-clientfio-error').html(null);
                 if(shippingErrors.clientFio.errors)
                     for(i=0; i<shippingErrors.clientFio.errors.length; i++)
                         $('#shipping-clientfio-error').append('<p>'+shippingErrors.clientFio.errors[i]+'</p>');


                 $('#shipping-clienttel-error').html(null);
                 if(shippingErrors.clientTel.errors)
                     for(i=0; i<shippingErrors.clientTel.errors.length; i++)
                         $('#shipping-clienttel-error').append('<p>'+shippingErrors.clientTel.errors[i]+'</p>');


                 $('#shipping-company-error').html(null);
                 if(shippingErrors.company.errors)
                     for(i=0; i<shippingErrors.company.errors.length; i++)
                         $('#shipping-company-error').append('<p>'+shippingErrors.company.errors[i]+'</p>');


                 $('#shipping-storageaddress-error').html(null);
                 if(shippingErrors.storageAddress.errors)
                     for(i=0; i<shippingErrors.storageAddress.errors.length; i++)
                         $('#shipping-storageaddress-error').append('<p>'+shippingErrors.storageAddress.errors[i]+'</p>');


                 $('#shipping-storagenum-error').html(null);
                 if(shippingErrors.storageNum.errors)
                     for(i=0; i<shippingErrors.storageNum.errors.length; i++)
                         $('#shipping-storagenum-error').append('<p>'+shippingErrors.storageNum.errors[i]+'</p>');

             }
             else{
                 window.location.replace(Routing.generate('app_home_home'));
             }
         }

     });

}
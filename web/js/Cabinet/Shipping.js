$(document).ready(function(){
    $('button#shipping_delete').html('<span>Delete</span>');
    $('button#shipping_save').html('<span>Save</span>');
    $(document).on("change", "select[id$='shipping_shipping_select']", function(){
        var selectedId = $("select[id$='shipping_shipping_select']").val();
        if(selectedId){
            $.ajax({
                method: 'POST',
                url: Routing.generate('app_usercabinet_cabinet_getshipping', {id: selectedId}),
                success: function(response){
                    var shipping = JSON.parse(response);
                    console.log(shipping);
                    updateForm(shipping);
                }
            });
        }
        else clearForm();
    });
});


function updateForm(shipping)
{
    var context = $('form[name="shipping"], form[name="booking"]');
    $("input[id$='shipping_title']", context).val(shipping.title);
    $("input[id$='shipping_company']", context).val(shipping.company);
    $("input[id$='shipping_storageNum']", context).val(shipping.storage_num);
    $("input[id$='shipping_city']", context).val(shipping.city);
    $("input[id$='shipping_storageAddress']", context).val(shipping.storage_address);
    $("input[id$='shipping_clientFio']", context).val(shipping.client_fio);
    $("input[id$='shipping_clientTel']", context).val(shipping.client_tel);
}

function clearForm()
{
    var context = $('form[name="shipping"], form[name="booking"]');
    $("input[id$='shipping_title']", context).val('');
    $("input[id$='shipping_company']", context).val('');
    $("input[id$='shipping_storageNum']", context).val('');
    $("input[id$='shipping_city']", context).val('');
    $("input[id$='shipping_storageAddress']", context).val('');
    $("input[id$='shipping_clientFio']", context).val('');
    $("input[id$='shipping_clientTel']", context).val('');
}
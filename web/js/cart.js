$(document).ready(function(){

    updateMiniCart('app_cart_update', null);

    $(document).on("click", ".cart-product-info .remove-product", function()
    {
        updateMiniCart('app_cart_update', {id: $(this).html(), action: 'remove'});
    });

    $(document).on("click", ".add-to-cart", function(){
        var productId = $(this).parent().find('span:first').text();
        updateMiniCart('app_cart_update', {id: productId, action: 'toggle'});
        if($('i.fa-shopping-cart',this).hasClass('fa-icon-red'))
            $('i.fa-shopping-cart',this).removeClass('fa-icon-red');
        else
            $('i.fa-shopping-cart',this).addClass('fa-icon-red');
    });


    function updateMiniCart(controllerActionPathName, pathParams)
    {
        var tpl = $('<div><div class="cart-products"><div class="cart-image"><a href="#"><img src="" alt=""></a></div><div class="cart-product-info"> <a href="#" class="product-name"> Donec ac tempus </a> <a class="edit-product">Edit item</a> <a class="remove-product">Remove item</a> <div class="price-times"> <span class="p-price">$100.00</span> </div> </div> </div></div>');
        var outHtml = '';
        var totalPrice = 0;
        $.ajax
        ({
            method: "POST",
            url: Routing.generate(controllerActionPathName, pathParams, true),
            success: function(response)
            {
                var products = JSON.parse(response);
                if(typeof products === 'object')
                    products = Object.values(products);
                console.log(products);
                for(var i=0; i<products.length; i++ )
                {
                    var productTpl = tpl;
                    $('.product-name', productTpl).html(products[i].title);
                    $('.p-price', productTpl).html(products[i].price_disc +' '+ products[i].currency.name);
                    $('.cart-image img', productTpl).attr('src', products[i].mini_cart_photo_path);
                    $('.cart-image a', productTpl).attr('href', Routing.generate('app_product_show', {id: products[i].id}));
                    $('.cart-product-info .remove-product', productTpl).html(products[i].id);
                    outHtml += productTpl.html();
                    totalPrice += products[i].price_disc;
                    //console.log(productTpl);
                }
                $('.cart-products-list').html(outHtml);
                if(products[0])
                    $('.price-amount span').html(totalPrice.toFixed(2) +' '+ products[0].currency.name);
                else
                    $('.price-amount span').html(null);

                $('.header-r-cart .cart span').html(products.length);
                //console.log(products);
                $('.header-r-cart').css('display', 'block');
            }
        });
    }

});

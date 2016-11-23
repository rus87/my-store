$(document).ready(function(){

    $(document).on("click", ".add-to-cart", function(){
        var productId = $(this).parent().find('span.product-id:first').text();
        refreshWishlist('app_usercabinet_cabinet_wishlistupdate',
            {id: productId, action: 'remove'});

    });

    $(document).on("click", ".remove-from-wl", function(e){
        e.preventDefault();
        e.stopImmediatePropagation();
        var productId = $(this).parent().parent().find('span.product-id:first').text();
        refreshWishlist('app_usercabinet_cabinet_wishlistupdate', {id: productId, action: 'remove'});
    });

});

function refreshWishlist(routeName, params)
{
    var prototype = $("tr.row-prototype");
    $.ajax({
        method: 'POST',
        url: Routing.generate(routeName, params),
        success:function(response){
            if(response != 'null'){
                //console.log(response);
                var products = JSON.parse(response);
                if(typeof products === 'object')
                    products = Object.values(products);
                console.log(products);
                var outHtml = '';
                for(var i=0; i<products.length; i++){
                    var link = Routing.generate('app_product_show', {id: products[i].id});
                    $('div.price-box p.old-price', prototype).remove();
                    if(products[i].discount != null) {
                        var oldPrice = $('<p class="old-price"><span class="price"></span> </p>');
                        $('span.price', oldPrice).html(products[i].price +' '+ products[i].currency.name);
                        oldPrice.prependTo($('div.price-box', prototype));
                    }
                    $('td.product-img a', prototype).attr('href', link);
                    $('td.product-img img.img-responsive', prototype).attr('src', products[i].wishlist_thumb_path);
                    $('h6 a', prototype).attr('href', link);
                    $('h6 a', prototype).html(products[i].title);
                    $('p.description', prototype).html(products[i].description);
                    $('div.price-box span.special-price', prototype).html(products[i].price_disc +' '+ products[i].currency.name);
                    $('span.product-id', prototype).html(products[i].id);
                    var removeLink = Routing.generate('app_usercabinet_cabinet_wishlistupdate',
                        {'id': products[i].id, 'action': 'remove'});
                    $('td.p-action a').attr('href', removeLink);
                    outHtml += '<tr class="product-row">' + prototype.html() + '</tr>';
                    //console.log(prototype.html());
                }
                //console.log(outHtml);
                $('table.cart-table tbody tr.product-row').remove();
                $('table.cart-table tbody').prepend(outHtml);
            }
            else
                $('table.cart-table').remove();
        }
    });
}
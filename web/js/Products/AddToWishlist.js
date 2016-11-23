$(document).ready(function() {

    $(document).on("click", ".link-wishlist", function () {
        var productContext = $(this).parent().parent().parent();
        var productId = $("span.product-id", productContext).text();

        $.ajax({
            method: "POST",
            url: Routing.generate('app_usercabinet_cabinet_wishlistupdate', {id: productId, action: 'toggle'}),
            success: function(response){
                //console.log(response);
            }
        });
        if($('i.fa-heart',this).hasClass('fa-icon-red'))
            $('i.fa-heart',this).removeClass('fa-icon-red');
        else
            $('i.fa-heart',this).addClass('fa-icon-red');
    });
});
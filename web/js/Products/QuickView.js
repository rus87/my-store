$(document).ready(function(){

    $(document).on("click", ".btn-quickview", function(){
        var productContext = $(this).parent().parent().parent().parent().parent();

        var description = $('.product-description', productContext).html();
        var price = $('.price-box .special-price .price', productContext).html();
        var title = $('.single-product-name a', productContext).html();
        var photo = $('.product-image', productContext).find('img:first').attr('src');

        var modalContext = $('#quickview-wrapper');
        $('.product-images .main-image', modalContext).find('img:first').attr('src', photo);
        $('h1', modalContext).text(title);
        $('.special-price .amount', modalContext).text(price);
        $('.quick-desc', modalContext).text(description);
    });

});

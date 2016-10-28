$(document).ready(function(){

    $.ajax({
        method: "POST",
        url: Routing.generate('app_products_getcatstree'),
        success: function(response){
            var catsTree = JSON.parse(response);
            console.log(catsTree);
            each()
        }
    });
});
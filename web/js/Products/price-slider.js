$( function() {

    var initValues
    $.ajax({
        method: "POST",
        url: Routing.generate('app_products_pricesliderinit'),
        success: function(response){
            initValues = JSON.parse(response);
            console.log(initValues);

            $( "#slider-range" ).slider({
                range: true,
                min: initValues.min,
                max: initValues.max,
                values: [initValues.rangeMin, initValues.rangeMax],
                slide: function( event, ui ) {
                    $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
                    $( ".price_filter #price-min").val(ui.values[ 0 ]);
                    $( ".price_filter #price-max").val(ui.values[ 1 ]);
                }
            });
            $( "#amount" ).val( "$" + $( "#slider-range" ).slider( "values", 0 ) +
                " - $" + $( "#slider-range" ).slider( "values", 1 ) );
        }
    });

} );

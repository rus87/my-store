$(document).ready(function(){
    $('.sidebar-category-list li[class="has-children"] a').mouseenter(function(){
        $('ul.inner-cats', $(this).parent()).slideDown(200);
        $("ul.inner-cats  ul.inner-cats", $(this).parent()).css('display', 'none');
    });
    $('.sidebar-category-list li[class="has-children"]').mouseleave(function(){
        $('ul.inner-cats', $(this).parent()).slideUp(200, function() {
        });

    });
});
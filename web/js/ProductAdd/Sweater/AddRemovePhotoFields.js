function add_file_input()
{
    var photos_count = $('#sweater_photos').children().length;
    var prototype = $('#sweater_photos').attr('data-prototype');
    var prototype_modified = prototype.replace(/__name__label__/g, '');
    var prototype_modified1 = prototype_modified.replace(/__name__/g, photos_count);


    $('#sweater_photos').append(prototype_modified1);
}

$(document).ready(function()
{
    $(document).on("click", ".del_input", function()
    {
        $(this).parent().parent().parent().remove();
    });
});

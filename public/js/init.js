(function ($) {
    $(function () {

        $('.sidenav').sidenav();
        $('.parallax').parallax();
        $('.tabs').tabs();
        $('.fixed-action-btn').floatingActionButton();
        $('.tap-target').tapTarget();
        $('.modal').modal();
        $('.tooltipped').tooltip();
        $('select').formSelect();
        //M.Modal.getInstance($('#newPostModal')).open();
        $.ajax({
            method: 'POST',
            url: '/ajax/getalltags'
        }).done(function (data) {
            tags = [];
            for (i=0; i < data.tags.length; i++) {
                tag = data.tags[i]
                tags[tag.name] = null
            }
            let options = {
                placeholder: 'Tags',
                autocompleteOptions: {
                    data: tags,
                    limit: 5,
                    minLength: 1
                }
            };
            if ($('#post_form_newTags').val()){ //On post edit page
                let autocomplete = [],
                    tags = jQuery.parseJSON($('#post_form_newTags').val())

                for (tag in tags) {
                    let value = tags[tag]['tag']
                    autocomplete.push({'tag': value})
                }
                options['data'] = autocomplete
            }
            $('.chips').chips(options);

        });

        $('textarea#postcontent').characterCounter();

        $("form#newPostForm").submit(function () {
            var $input = $(this).find("input[name=tags_value]");
            $input.val(JSON.stringify(M.Chips.getInstance($('.chips')).chipsData));

        });


    }); // end of document ready
})(jQuery); // end of jQuery name space


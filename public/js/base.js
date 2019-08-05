$(document).ready(function () {
    $('.js_like_button').on('click', function (e) {
        e.preventDefault()

        var $link = $(e.currentTarget)

        $.ajax({
            method: 'POST',
            url: $link.attr('href')
        }).done(function (data) {
            if (data.error != null) {
                M.toast({html: data.error, classes: "red"});
            } else {
                if (data.status === true) {
                    $(e.target).removeClass("white green-text").addClass("green white-text")
                    M.toast({html: 'Le post a bien été liké', classes: "green"});
                } else {
                    $(e.target).removeClass("green white-text").addClass("white green-text")
                }
            }
            $("#post-likes-" + $link.attr('post-id')).html("Likes : " + data.count)
        })
    })
})
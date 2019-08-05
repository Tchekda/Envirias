(function($){
    $(function(){

        $('.sidenav').sidenav();
        $('.validate-btn').on('click', function (e) {
            e.preventDefault()
            let html = '<form action="" method="POST"><span>Combien de points ? <input value"1" id="value" type="number"></span><button type="submit" class="btn-flat toast-action">Valider</button></form>'
            M.toast({
                html: html, classes: 'rounded green',  completeCallback: function () {
                    alert($('#value').val())
                }
            })


        });
    }); // end of document ready
})(jQuery); // end of jQuery name space
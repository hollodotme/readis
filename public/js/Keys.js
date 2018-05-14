$(document).ready(function () {
    let keysContainer = $('#keys');
    let keySelectForm = keysContainer.find('#key-select-form');
    let databaseSelect = keysContainer.find('#database-select');
    let limitSelect = keysContainer.find('#limit-select');
    let ajaxSpinner = $('#ajaxSpinner');
    let body = $('body');
    keySelectForm.submit(function (e) {
        let formData = $(this).serialize();
        let formUrl = $(this).attr('action');
        ajaxSpinner.show();
        $('#keyValues').load(formUrl + '?' + formData, function () {
            ajaxSpinner.hide();
            $('#searchPattern').focus();
        });
        e.preventDefault();
    });

    databaseSelect.find('a').click(function () {
        let database = $(this).data('database');
        let searchRegExp = new RegExp(/\/database\/\d+\//);
        let newAction = keySelectForm.attr('action').replace(searchRegExp, '/database/' + database + '/');
        keySelectForm.attr('action', newAction);
        keysContainer.find('#current-db').html($(this).html());
        keySelectForm.submit();
    });

    limitSelect.find('a').click(function () {
        let limit = $(this).data('limit');
        keySelectForm.find('input[name="limit"]').val(limit);
        keysContainer.find('#current-limit').html($(this).html());
        keySelectForm.submit();
    });

    $('[data-toggle="tooltip"]').tooltip();

    body.on('hidden.bs.modal', '.modal', function () {
        $(this).removeData('bs.modal');
    });

    body.on('click', '[data-toggle="modal"]', function () {
        let modalContent = $(this).data("target") + ' .modal-content';
        $.get($(this).attr("href")).done(function (content) {
            $(modalContent).html(content);
        }).fail(function () {
            $(modalContent).html(
                '<div class="modal-header">\n' +
                '    <span class="modal-title">\n' +
                '\t\t<span class="text-danger">ERROR</span>\n' +
                '    </span>\n' +
                '\t<button type="button" class="close" data-dismiss="modal" aria-label="Close">\n' +
                '\t\t<span aria-hidden="true">&times;</span>\n' +
                '\t</button>\n' +
                '</div>\n' +
                '<div class="modal-body">\n' +
                '\t<div>\n' +
                '\t\t<h4>Could not load data for key.</h4>\n' +
                '\t\t<p>\n' +
                '\t\t\tMaybe your view is outdated and has diverged from the actual redis data.\n' +
                '\t\t</p>\n' +
                '\t\t<p>\n' +
                '\t\t\tPlease reload the server details page and try again.\n' +
                '\t\t</p>\n' +
                '\t</div>\n' +
                '</div>\n' +
                '<div class="modal-footer">\n' +
                '\t<div class="container mt-0 mb-0 p-0">\n' +
                '\t\t<div class="row">\n' +
                '\t\t\t<div class="col align-self-end text-right">\n' +
                '\t\t\t\t<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\n' +
                '\t\t\t</div>\n' +
                '\t\t</div>\n' +
                '\t</div>\n' +
                '</div>\n'
            );
        });
    });

    keySelectForm.submit();
});

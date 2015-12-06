/**
 * Created by hollodotme on 13/09/15.
 */

$(document).ready(function () {
    var keysContainer = $('#keys');
    var keySelectForm = keysContainer.find('#key-select-form');
    var databaseSelect = keysContainer.find('#database-select');
    var limitSelect = keysContainer.find('#limit-select');
    var ajaxSpinner = $('#ajaxSpinner');

    keySelectForm.submit(function (e) {
        var formData = $(this).serialize();
        var formUrl = $(this).attr('action');
        ajaxSpinner.show();
        $('#keyValues').load(formUrl + '?' + formData, function () {
            ajaxSpinner.hide();
            $('#searchPattern').focus();
        });
        e.preventDefault();
    });

    databaseSelect.find('a').click(function () {
        var database = $(this).data('database');
        keySelectForm.find('input[name="database"]').val(database);
        keysContainer.find('#current-db').html($(this).html());
        keySelectForm.submit();
    });

    limitSelect.find('a').click(function () {
        var limit = $(this).data('limit');
        keySelectForm.find('input[name="limit"]').val(limit);
        keysContainer.find('#current-limit').html($(this).html());
        keySelectForm.submit();
    });

    $('[data-toggle="tooltip"]').tooltip();

    $('body').on('hidden.bs.modal', '.modal', function () {
        $(this).removeData('bs.modal');
    });

    keySelectForm.submit();
});
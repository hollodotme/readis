/**
 * Created by hollodotme on 13/09/15.
 */

$(document).ready(function () {
    var keysContainer = $('#keys');
    var keySelectForm = keysContainer.find('#key-select-form');
    var databaseSelect = keysContainer.find('#database-select');

    keySelectForm.submit(function (e) {
        var formData = $(this).serialize();
        var formUrl = $(this).attr('action');
        $('#keyValues').load(formUrl + '?' + formData);
        e.preventDefault();
    });

    databaseSelect.find('a').click(function (e) {
        var keyDb = $(this).data('keydb');
        keySelectForm.find('input[name="keydb"]').val(keyDb);
        keysContainer.find('#current-db').html($(this).html());
        keySelectForm.submit();
    });

    $('[data-toggle="tooltip"]').tooltip();
});
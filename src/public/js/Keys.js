/**
 * Created by hollodotme on 13/09/15.
 */

$(document).ready(function () {
    var keysContainer = $('#keys');
    var keySelectForm = keysContainer.find('#key-select-form');
    var databaseSelect = keysContainer.find('#database-select');

    databaseSelect.find('a').click(function (e) {
        var keyDb = $(this).data('keydb');
        keySelectForm.find('input[name="keydb"]').val(keyDb);
        keysContainer.find('#current-db').html('Database ' + keyDb);
    });
});
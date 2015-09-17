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
            $.initKeyInfoModal();
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

    $.initKeyInfoModal();

    keySelectForm.submit();
});

$.initKeyInfoModal = function () {
    $('#keyInfoModal').on('show.bs.modal', function (e) {
        var btn = $(e.relatedTarget);
        var modal = $(this);
        var modalBody = modal.find('.modal-body');
        var ajaxUrl = modal.data('load-url');
        var database = modal.data('database').toString();
        var server = modal.data('server');
        var key = btn.data('key');
        var params = {action: 'getKeyData', database: database, server: server, key: key};
        var loadUrl = ajaxUrl + '?' + $.param(params);

        modal.find('#keyInfoModalLabel').html('Key: <code>' + key + '</code>');
        modalBody.load(loadUrl, function () {
        });
    });
};
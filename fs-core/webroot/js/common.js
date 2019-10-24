var showAlert = function ($msg) {
    $('#modal-dialog').find('.modal-body').html($msg);
    $('#modal-dialog').modal('show');
};

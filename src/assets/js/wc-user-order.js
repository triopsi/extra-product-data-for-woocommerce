jQuery(document).ready(function ($) {
    $('.exprdawc-edit-order-item').on('click', function () {
        const itemId = $(this).data('item-id');
        $(`#exprdawc-order-item-fields-${itemId}`).toggle();
    });

    $('.exprdawc-save-order-item').on('click', function () {
        const itemId = $(this).data('item-id');
        const requestData = $.extend({}, {
            action: 'exprdawc_save_order_item_meta',
            security: exprdawc_user_order.nonce,
            item_id: itemId,
            dataType: 'json',
        });
        const formElement = $(this).closest('.exprdawc-order-item-fields').find('form')[0];
        if (formElement.reportValidity() !== true) {
            return;
        }
        const formData = new FormData(formElement);
        for (const property in requestData) {
            formData.append(property, requestData[property]);
        }
        $.ajax({
            url: exprdawc_user_order.ajax_url,
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: (response) => {
                if (response.data && response.success) {
                    alert(exprdawc_user_order.success_message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            },
            error: () => {
                window.alert(exprdawc_user_order.error_message);
            }
        });
    });
});
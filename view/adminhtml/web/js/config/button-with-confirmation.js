define([
    'ko',
    'uiComponent',
    'jquery',
    'mageUtils',
    'Magento_Ui/js/modal/confirm'
], function (ko, Component, $, utils, confirm) {
    'use strict';

    return Component.extend({
        defaults: {
            buttonLabel: '',
            template: 'Freento_SqlLog/config/simple-button',
            ajaxUrl: '',
            confirmationMessage: ''
        },

        /**
         *  Button action
         */
        action: function () {
            const ajaxUrl = this.ajaxUrl;
            confirm({
                content: this.confirmationMessage,
                actions: {
                    confirm: function () {
                        utils.submit({
                            url: ajaxUrl
                        });
                    }
                }
            })
        }
    });
});

define([
    'jquery',
    'Magento_Ui/js/grid/provider'
], function ($, gridProvider) {

    return gridProvider.extend({
        defaults: {
            additionalDataHtmlComponent: 'h1.page-title > div.additional-data'
        },

        /**
         * Initializes dataStorage configuration.
         *
         * @returns {Provider} Chainable.
         */
        initConfig: function () {
            let result = this._super();
            this.updateTitle();
            return result;
        },

        /**
         * Add query text into title block
         */
        updateTitle: function () {
            if (this.additionalData) {
                let queryTitle = $(this.additionalDataHtmlComponent);
                if (!queryTitle.length) {
                    $('h1.page-title').append(
                        '<div class="additional-data"' +
                        ' style="font-size: 2rem; display: grid; grid-template-columns: auto 1fr; column-gap: 5px">' +
                        '</div>'
                    );
                    queryTitle = $(this.additionalDataHtmlComponent);
                }
                for (let key in this.additionalData) {
                    queryTitle.append(
                        '<div>' + key + ':</div>'
                        + '<div style="max-height: 200px; overflow: auto; padding: 1px">' + this.additionalData[key] + '</div>'
                    )
                }
            }
        },

        /**
         * Handles changes of 'params' object.
         */
        onParamsChange: function () {
            const urlParams = new URLSearchParams(window.location.search);
            const keys = urlParams.keys();
            for (let key of keys) {
                this.params[key] = urlParams.get(key);
            }

            this._super();
        },

        /**
         * Handles successful data reload.
         *
         * @param {Object} data - Retrieved data object.
         */
        onReload: function (data) {
            if (data.errorMessage && !this.errorMessageIsShown) {
                this.errorMessageIsShown = true;
                $('body').notification('add', {
                    error: true,
                    message: data.errorMessage,

                    /**
                     * @param {String} message
                     */
                    insertMethod: function (message) {
                        var $wrapper = $('<div></div>').html(message);

                        $('.page-main-actions').after($wrapper);
                    }
                })
            }

            this._super(data);
        },

        getAdditionalData: function () {
            return this.additionalData ? this.additionalData : {};
        },
    });
});

export default  {
    created: function () {
        // connected
    },

    methods: {

        /**
         * Validity promise.
         * @param res
         * @param invalidError
         * @returns {Promise}
         */
        validCheck(res, invalidError){
            return new Promise((resolve, reject) => {
                if (res) {
                    resolve();
                    return;
                }

                toastr.error(invalidError, 'Check failed', {
                    timeOut: 2000, preventDuplicates: true
                });
                reject();
            });
        },

    }
}

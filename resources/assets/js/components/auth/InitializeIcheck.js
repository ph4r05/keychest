export default {
  methods: {
    initialitzeICheck (field) {
        const component = this;
        const compObj = $('input[name=' + field + ']');

        $(document).ready(() => {
            setTimeout(() => {
                compObj.iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                    radioClass: 'iradio_square-blue',
                    increaseArea: '20%',
                    tap: true
                });
            }, 10);

            setTimeout(() => {
                compObj.on('ifChecked', function (event) {
                    component.form.setField(field, true);
                    component.form.errors.clear(field);
                });

                compObj.on('ifUnchecked', function (event) {
                    component.form.setField(field, '');
                });

            }, 10);

        });
    }
  }
}

import isEmail from 'validator/lib/isEmail';
import ph4 from '../ph4';

export default {
    getMessage(field, args, data) {
        return 'Input is neither valid PGP ID nor email address';
    },

    validate(value, args) {
        // Either email or PGP key ID (hexa)
        const re_id = new RegExp(/^\s*(0x)?([0-9a-fA-F]{4,16})\s*$/, '');
        const m1 = value.match(re_id);
        if (m1){
            return {valid: true, data: 1};
        }

        if (isEmail(value, {allow_display_name: true})){
            return {valid: true, data: 2};
        }

        return {valid: false, data: 0};
    }
};


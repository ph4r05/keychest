import isIP from 'validator/lib/isIP';

export default {
    getMessage(field, args, data) {
        const [matchType, code] = data;
        if (matchType === 0) {
            return 'Input does not conform to IP range format';
        }

        if (matchType === 1){
            if (code === 1){
                return 'The IP range start is not valid IP address';
            } else {
                return 'The IP range stop is not valid IP address';
            }
        }

        if (code === 1){
            return 'The IP range start is not valid IP address';
        } else {
            return 'The IP range size is not valid';
        }
    },
    validate(value, args) {
        // First re check - global format. IP looking separated by dash OR IP looking slash range
        // Another check - check the IP validity itself
        const re1 = new RegExp(/^\s*([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\s*-\s*([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\s*$/, '');
        const re2 = new RegExp(/^\s*([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\s*\/\s*([0-9]{1,2})\s*$/, '');

        const m1 = value.match(re1);
        const m2 = value.match(re2);
        if (!m1 && !m2){
            return {valid: false, data: [0,0]};
        }
        
        if (m1){
            if (!isIP(m1[1], 4)){
                return {valid: false, data: [1,1]};
            }

            if (!isIP(m1[2], 4)){
                return {valid: false, data: [1,2]};
            }

            return {valid: true, data: [1,0]};

        } else {
            if (!isIP(m2[1], 4)){
                return {valid: false, data: [2,1]};
            }

            const range = +m2[2];
            if(range<0 || range>32){
                return {valid: false, data: [2,2]};
            }

            return {valid: true, data: [2,0]};
        }
    }
};


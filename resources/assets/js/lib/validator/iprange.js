import isIP from 'validator/lib/isIP';
import ipaddr from 'ipaddr.js';
import ph4 from '../ph4';

export default {
    getMessage(field, args, data) {
        const [matchType, code] = data;
        if (matchType === 0) {
            return 'Input does not conform to IP range format';
        }

        if (matchType === 1){
            if (code === 1){
                return 'The IP range start is not valid IP address';
            } else if (code === 2) {
                return 'The IP range stop is not valid IP address';
            } else if (code === 3){
                return 'The IP range start address is greater than IP range stop address';
            }
        }

        if (code === 1){
            return 'The IP range start is not valid IP address';
        } else if (code === 2) {
            return 'The IP range size is not valid';
        } else if (code === 4) {
            return 'The IP range size is too big';
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

            const ip1 = ipaddr.IPv4.parse(m1[1]).toByteArray();
            const ip2 = ipaddr.IPv4.parse(m1[2]).toByteArray();
            if (ph4.arr_cmp(ip1, ip2) === 1){
                return {valid: false, data: [1,3]};
            }

            if (args && ph4.ip_range(ip1, ip2) > args[0]){
                return {valid: false, data: [2,4]};
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

            if (args && 2**(32-range) > args[0]){
                return {valid: false, data: [2,4]};
            }

            return {valid: true, data: [2,0]};
        }
    }
};


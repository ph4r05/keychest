import _ from 'lodash';
import ipaddr from 'ipaddr.js';

export default {
    /**
     * Compare arrays by elements
     * @param a
     * @param b
     * @returns {number}
     */
    arr_cmp(a, b) {
        if (!a && !b){
            return 0;
        } else if (!a){
            return -1;
        } else if (!b){
            return 1;
        }

        const min_len = Math.min(a.length, b.length);
        for(let i=0; i<min_len; i++){
            const ai = a[i];
            const bi = b[i];
            if (ai < bi){
                return -1
            } else if (ai > bi){
                return 1;
            }
        }

        return 0;
    },

    /**
     * Computes IP range from the IP addresses, IPv4
     * @param ip_start
     * @param ip_stop
     */
    ip_range(ip_start, ip_stop){
        if (_.isString(ip_start)){
            ip_start = ipaddr.parse(ip_start).toByteArray();
        }
        if (_.isString(ip_stop)){
            ip_stop = ipaddr.parse(ip_stop).toByteArray();
        }

        let size = 0;
        for(let i=0; i<4; i++){
            if (ip_stop[i] === ip_start[i]){
                continue;
            }

            size += (ip_stop[i] - ip_start[i]) * (2**((3-i)*8));
        }
        return size + 1;
    }


}


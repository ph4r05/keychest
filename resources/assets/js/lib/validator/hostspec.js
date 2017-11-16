import isIP from 'validator/lib/isIP';
import ipaddr from 'ipaddr.js';
import _ from 'lodash';

export default {
    getMessage(field, args, data) {
        const [matchType, code] = data;
        if (matchType === 0) {
            return 'Input does not conform to host address format';
        }

        if (matchType === 1 || matchType === 3){
            if (code === 3){
                return 'The IP address and port are not valid';
            } else if (code === 2) {
                return 'The port is not valid';
            } else if (code === 1){
                return 'The IP address is not valid';
            }
        }

        if (matchType === 4){
            if (code === 2){
                return 'The port is not valid';
            }
        }
    },

    validate(value, args) {
        // First re check - global format. IP looking separated by dash OR IP looking slash range
        // Another check - check the IP validity itself

        // spec1: IPV4
        // spec2: IPV4:port
        // spec3: [IPV6]
        // spec4: [IPV6]:port
        // spec5: hostname
        // spec6: hostname:port

        const isPortOk = (portNum) => {
            if (_.isEmpty(portNum)){
                return true;
            }

            const p = Number(portNum);
            return p > 0 && p <= 65535;
        };

        const ipv4Re = new RegExp(/^\s*([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})(?::([0-9]{1,5}))?\s*$/, '');
        const ipv6NoPortRe = new RegExp(/^\s*([0-9a-fA-F:]+)\s*$/, '');
        const ipv6WithPortRe = new RegExp(/^\s*\[([0-9a-fA-F:.]+)\]:([0-9]{1,5})\s*$/, '');
        const hostnameRe = new RegExp(/^\b((?=[a-z0-9-]{1,63}\.)(xn--)?[a-z0-9]+(-[a-z0-9]+)*\.)+([a-z]{2,63})\b(?::([0-9]{1,5}))?$/, '');

        const mIpv4 = value.match(ipv4Re);
        const mIpv6 = value.match(ipv6WithPortRe);
        const mHostname = value.match(hostnameRe);

        if (mHostname){
            const portOk = isPortOk(mHostname[5]);
            const code = portOk ? 0 : 2;
            return {valid: portOk, data: [4, code]};
        }

        if (mIpv6){
            const isIpOk = isIP(mIpv6[1], 6) && ipaddr.IPv6.isValid(mIpv6[1]);
            const portOk = isPortOk(mIpv6[2]);
            const code = (isIpOk ? 0 : 1) | (portOk ? 0 : 2);

            return {valid: isIpOk && portOk, data: [3, code]};

        }

        if (mIpv4){
            const isIpOk = isIP(mIpv4[1], 4) && ipaddr.IPv4.isValid(mIpv4[1]);
            const portOk = isPortOk(mIpv4[2]);
            const code = (isIpOk ? 0 : 1) | (portOk ? 0 : 2);

            return {valid: isIpOk && portOk, data: [1, code]};
        }

        return {valid: false, data: [0, 0]};
    }
};


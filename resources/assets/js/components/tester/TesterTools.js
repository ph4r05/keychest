import _ from 'lodash';
import moment from 'moment';
import sprintf from 'sprintf-js';
import Req from 'req';
import ph4 from 'ph4';

export default  {
    bitLen(mod) {
        if (!mod || !_.isString(mod)){
            return undefined;
        }

        const offset = _.startsWith(mod, '0x') ? 2 : 0;
        return (mod.length - offset) * 4;
    },

    keyType(ktype){
        if (ktype === 'ssh-rsa'){
            return 'SSH RSA';
        } else if (ktype === 'mod-hex'){
            return 'RSA modulus (hex)';
        } else if (ktype === 'mod-base64'){
            return 'RSA modulus (base64)';
        } else if (ktype === 'mod-dec'){
            return 'RSA modulus (dec)';
        } else if (ktype === 'pem-rsa-key'){
            return 'RSA key';
        } else if (ktype === 'pgp'){
            return 'PGP key';
        } else if (ktype === 'apk-pem-cert'){
            return 'Android Application Certificate';
        } else if (ktype === 'der-cert'){
            return 'X509 certificate (DER)';
        } else if (ktype === 'pem-cert'){
            return 'X509 certificate (PEM)';
        } else if (ktype === 'pkcs7-cert'){
            return 'SMIME / PKCS7 certificate';
        } else if (ktype === 'ldiff-cert'){
            return 'LDAP certificate';
        } else if (ktype === 'jks-cert'){
            return 'JKS certificate';
        } else if (ktype === 'jks-cert-chain'){
            return 'JKS certificate chain';
        } else {
            return ktype;
        }
    }


}

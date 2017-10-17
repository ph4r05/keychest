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
            return 'SSH RSA key';
        } else if (ktype === 'mod-hex'){
            return 'RSA modulus (hex encoded)';
        } else if (ktype === 'mod-base64'){
            return 'RSA modulus (base64 encoded)';
        } else if (ktype === 'mod-dec'){
            return 'RSA modulus (decimal number)';
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
    },

    exampleCert(){
        return '-----BEGIN CERTIFICATE-----\n' +
            'MIICpTCCAYwCCQC2u0PIfFaGMjANBgkqhkiG9w0BAQsFADAUMRIwEAYDVQQDDAls\n' +
            'b2NhbGhvc3QwHhcNMTcxMDE2MTkzODIxWhcNMTgxMDE2MTkzODIxWjAUMRIwEAYD\n' +
            'VQQDDAlsb2NhbGhvc3QwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQJZ\n' +
            'J7UrpeaMjJJou5IY83ZzYUymVBj0dFsUPNTuU/lJHJoOHC8jqVFjBq/784ZnuHG8\n' +
            'DMguYPW7Gp+hWlZxp2XJ8huVh9gBFZZDcqODyIOw3L9sd1cGsx6v8+P9SIVZoIze\n' +
            'og+al8TFm2uKjuykV9SoINSVCfdZM2MCvKGjaQsICRgR+Fjy6M6lpiNVrW4EHRk1\n' +
            '7aWSibWXaDtz4mV650v/x2Dk1RPMh9uTVZGOqgjTmLvl9oNdyHElIRubNrOgvHC5\n' +
            'k6bLP30stAYd5z25cslCrfmVW2/kzZDwDQiK7ASvH17/kfIa9e1EYXx9uAk/lTZt\n' +
            'smWAxK85neuU+bFBMFvhAgMBAAEwDQYJKoZIhvcNAQELBQADggECAAG7W49CYRUk\n' +
            'YAFRGXu3M85MKOISyc/kkJ8nbHdV6GxJ05FkoDKbcbZ7nncJiIp2VMAMEIP4bRTJ\n' +
            '5U4g4vSZlmCs8BDmV3Ts/tbDCM6eqK+2hwjhUnCnmmsLt4xVUeAAsWUHl9AVtjzd\n' +
            'oYlm1Kk20QBzNpsvM/gFS5B+duHvTSfELfoq9Pdfvmn2gEXJHe9scO8bfT3fm15z\n' +
            'R6AUYsSsxAhup2Rix6jgJ14KGsh6uVm6jhz9aBTBcgx7iMuuP8zUbUE6nryHYXnR\n' +
            'cSvuYSesTCoFfnL7elrZDak/n0jLfwUD80aWnReJfu9QQGdqdDnSG8lSQ1XPOC7O\n' +
            '/hFW9l0TCzOE\n' +
            '-----END CERTIFICATE-----';
    },

    exampleSshKey(){
        return 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQGpxaaH24+yow+C3/jdoVTCke8nsEsl4YW4qyDExqX2BIWf5Z7eVKCvfA5yiIAK1xk7tzGlvx4RcNehxmkw1B6j6yHe33uK9dQqkcNPcgRblu/VfLGCMB3cwmln5VCOmriJmG1X6Dff5w+Z/DfEmPQ8F8O5sGTPcERD16Az4DqCFpeqvzD7Ke59M7N2eWWokFjjs5dUvTocAH0oojsM+nn51xGsbkxLT3ewY0sezWhZGvA8fVO2Kl8tkRY9TvT48mgxrkYe2F7F94jFEj/8Esh0IP3P6h0bSlLDUhgk+zmYNIc5tiqYbY52siibwuuLwG0TgUeFG4OgcYPSEFWfkGMh\n';
    },

    examplePubKey(){
        return '-----BEGIN PUBLIC KEY-----\n' +
            'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEBqcWmh9uPsqMPgt/43aFU\n' +
            'wpHvJ7BLJeGFuKsgxMal9gSFn+We3lSgr3wOcoiACtcZO7cxpb8eEXDXocZpMNQe\n' +
            'o+sh3t97ivXUKpHDT3IEW5bv1XyxgjAd3MJpZ+VQjpq4iZhtV+g33+cPmfw3xJj0\n' +
            'PBfDubBkz3BEQ9egM+A6ghaXqr8w+ynufTOzdnllqJBY47OXVL06HAB9KKI7DPp5\n' +
            '+dcRrG5MS093sGNLHs1oWRrwPH1TtipfLZEWPU70+PJoMa5GHthexfeIxRI//BLI\n' +
            'dCD9z+odG0pSw1IYJPs5mDSHObYqmG2OdrIom8Lri8BtE4FHhRuDoHGD0hBVn5Bj\n' +
            'IQIDAQAB\n' +
            '-----END PUBLIC KEY-----\n';
    },

    examplePgpKey(){
        return '-----BEGIN PGP PUBLIC KEY BLOCK-----\n' +
            'Version: GnuPG v2\n' +
            '\n' +
            'mQINBFkOI1wBEACSJphub008MSHXZ2xUMaeduzGH8yXOVpKGEMmEtCAVsfh8pZrm\n' +
            '+pi6B2GxZtWPIq0rtULmyIwpwtUnC8EMx8nllI/m1aesQz1kecaZZuiLy7gsmDOc\n' +
            'YbdFHHgcGK5bj7mULx+GfHQF86FIfRydHRpsKz+YTyV3j/33PdxDEZflVylG7UL5\n' +
            'evpU/W1vM8h2EzmCIUeXrb+B4zt+DQBc2XSuNpv0U39cKpTHY0xFAJqMQtZ503kz\n' +
            'aArNdOZCVqipuXzaT0EBQNWIRQzIXJp3YfJiW6aMrPED4pceV3NGegOcVjp/ZGRa\n' +
            'iX8qdXl6jQkcmziVYcBjNXSQ+Nq09Ld5ML2VqzyAwHvmxabT5kunFXnTv1N1Nw3K\n' +
            '8yCfpi9BZ9q1hYffNDZGvTG/jRkzy1mT9znOQxy0LGTy9VIZWJrHepTU9dnviG7S\n' +
            'xrQNsLap4VYHeLCaphhDb6hOhK1XUb6CMK3u/OfYqum0uAChuuZc1DgO6i3AvRBo\n' +
            '2wavX29tRklrxox/j4sbwVUWDydt8EMm5HceNE6nVwsMM/JnwaBo9U2nqoKEas3N\n' +
            '3VNOOLABONs42UOZqsFhib2HEaQNGnHVDwKhOb3lKSyaduVz4ervv9aXlLW1UlSZ\n' +
            'ZR5pc5ErVLhqL3tY57gK3zYm9U/y1Ij9+48rcK7kTZfWvaFwv/7m6dxVswARAQAB\n' +
            'tB10ZXN0QHRlc3QuY29tIDx0ZXN0QHRlc3QuY29tPokCOQQTAQgAIwUCWQ4jXAIb\n' +
            'AwcLCQgHAwIBBhUIAgkKCwQWAgMBAh4BAheAAAoJEIUFLWkVw03EVP4QAICO6x3D\n' +
            'qzB7y3IhIaWArqvkPfyKCuP81AL3ZZbSvpVZxcGokuwx43okuWdg0z5mG8WieYSq\n' +
            'e1UAqGDM5h29M3UsSZ/ROVWjX1j9oHNysGjkFARQQq7RUytqKHTHmQUhfHEtKQa5\n' +
            'CwaOiiqeOe2XVyGUqovU6zSLtdoKUMkNNds496HxDK57K+EIb6AYsOJxEsP1xtQN\n' +
            'sxSYfjdkCuhP+SIC0z7OZ7TX8jSMTdToZvIgIhHaNT6PTjAD/RXR8hsOVE/05lGl\n' +
            'Gikipd+W6NqrbcBd/XoEtb7/iOjaJ784uBFWFCxzGeyYaT3NJxZ6xDndhp6tctPn\n' +
            'smloL5jdZ3ihUl1UNitbUzCGOHRlyOwQb+7Hu5NAlAX6n+1l+j8yTdY6U7WF0Mxj\n' +
            'GjcVmHVWjk4jwU3Xx+Dyc4iU273tORE9Tkf0FHqPVNhe3uW2YWi1sSLatvlU9DxQ\n' +
            'AjFdXxfwcPLiU2JhjaJs7rglBMbtjvntvI+9xOb1R3DjBINeim923Zy5lNcKdl/L\n' +
            'Y4TwfMo7C/fFZrth5xncw/KUcGa2afLkGgyIYqK4DI12Ymui7ObSsh2CSLcxgRNM\n' +
            'Q6D3tMh0ZQwBvdzuEotLEmvSEUNyxPBr5H27eXMY7U4fJ7zIRszEaVGl9Y2YldHs\n' +
            'c9qc0tO0kXUB2HPdt0f20mWYDzSHq/UQd3dVuQIFBFkOI1wBD8CaZS8XSzb++Dcf\n' +
            'm2ejg0UkhbK3oFLcGPQj4tyAkQcWFWS4HrWbXGiI1O4Lukxrh2tXCcESzlvgmK0+\n' +
            'TRgzoC0KVn+XR8O9DhDiosPZHEm2A/eTF2CRkE5R8dDyOUm0Db1YekG/MKWJsGs5\n' +
            'AC3mV7bnlc1ibQmkx1Ydzmfn1p+c1/GmyX+kpWoEput16z4SeBxLrfobWgsudqYa\n' +
            'I+STiiExLVRzw11+y/IY28S3OZsBm/PBNIvhybw2rc/B68YFODBKfvYI0nMxip0s\n' +
            'gI+6B2vpiZTBKQV0EM7mMIzCWXQ+y2pFcCp2Cy1yT1VTf3gDbGhcX/nbxe9IJTV9\n' +
            'uD1tWTRNbO4beQDp43tX/AItPVu6IJIMJGfrJCaWms0o9uzhhw2qhs4QB0ePC3uY\n' +
            'SPcBaTxPfu5BtpNbo3JFV3W+D/iX83ixAw3ffOes77yexZQSNQr4AWN+lNI0XeaF\n' +
            'YzCxxHkg+i+hSqa6cYXAP991I6rOO9UQh+Q/Rm7v5lqBjCoqGm58tIPuCmGlxNVi\n' +
            'Fo8YgipyzDaE+DB313Aq0pvEWBLo+oruFq9JdqjqsCvmFAxqKPUYskNP7PSrnvg/\n' +
            'TM6Qqse7Sa54bUd25gALRzzYBwPno5aT7mUFbvua/6jx4lITTpM9vcE0d9dhmXKU\n' +
            'U1GbOopvzlZN4UWjKGUAEQEAAYkCHwQYAQgACQUCWQ4jXAIbIAAKCRCFBS1pFcNN\n' +
            'xIw5D/4wTOMI+mVjTFNmLXTMyPP0vgqkWkZXNwshhEAgplOj+QRCj8HmtXQ7/oX3\n' +
            'P23EU0HDCULvCNRIWtJFSG91Yexsl41eQHcsoapw0Q7a/SWCsb3oNogrG1PWkpH+\n' +
            'W/ioq8ltrFB+TkqKcC8SN6v6pKxvKllH0uktGf6TUKXqURSDUl41KmqkbuFjJy4V\n' +
            '/Tpp2MkY5ETmHpXWm8ypOh04k8KxuF4tkjU+0qVd0JPx9Oul49z1FSKm207Q9LNT\n' +
            '0vp4lRlsTaxFMKLBs+JdHH2BJtSRUfeBKDyX4tlcQMhec9jusZGqg4jh8yIgIa89\n' +
            'VjHdmSqpGIZf4moRmRfmE5FGeBvn2zenPd6DEguhXfwJIZlLQZwqTz/EEg0B/LRI\n' +
            'B7ek0guOod/gypW02WM3dkDcDhH9xTVFxzvIT0M0RRYh7mayQ3elf7Y2UuNkiU4U\n' +
            'jBQuftaimv2zv0TQRd6EQR9hnyuN8++WJcdK1zRYQ4nmKc4yT3N9DLXMjQ8GaQv4\n' +
            '+sguui0s4ILHCpI4pYEKf7eD9dv9wLuWOaWgvXKT8XDaPfId+IzICaPt0vDTkaaQ\n' +
            'DeZUN9X3SPIV+Jy18LsuNdyp7To4G3aAFWUH5nt9cCI1lgV4SQlFI02YiipQzi+Z\n' +
            'N6nln8KOe7u758ba2cjN7Wc/rKH6HZG5QLQxzBk53Nl7iEdEerkB/QRZDiNcAQ+A\n' +
            'k9dPEx/DVgd67in9Uo6QjHjGUE492mNtk2qkwFpieeQxwBLy/OEH6bGBczbsHsUc\n' +
            'Oa6/9zdmWC+8bzc1wXZLvcVDpOm1q5GJ3BrH7D+CMFdE3KSFxFKIDqcDHR6SbsSg\n' +
            'DstAc+r3Nd2MRo3h9Em9Z9NhYcMKXsnQ6vRXs2CHhNi+O+QkCNdRcR4Sl4dXLJSr\n' +
            'BrtG/BuITPkSiEO8hvcs/vZMZYMp0rwNnWMuxN/H1n08mFooFy5QlTvBW+1z37Ig\n' +
            'O/YKR2FrzdJRAhI6U6NxCA+lEE+XQkpv7We8g+2KhzGoVR/Wa6vb69m9SrWJZWpN\n' +
            '5sn9FQJjLthJi14bfjfsM4/xmpG5dKyS/BZsR9DwPRDQ3MPOdqNrExlzsbNnUlkt\n' +
            'wX0PTOZwneRK4dLHAugZioH3saHBIphHFnWshrt14sRiNhl0e7V4gNEMxf9LPRyc\n' +
            'qQglb+xSAyoSMHUVAMu8hNEl2Wxgv1o9a/kiQBl8MkRFXyK0hlS34r9YwDOxcCK4\n' +
            'Kav4VysdT1zVEr9HL+v0xZgZxwCtbXkpxcsAi1dUz1F20AsCTSxkExCHyKTcnbBh\n' +
            'VVivBB2NVDt7IcnF8Rg4Lb6+E56VuK58gQ8zD3w74aYnPXWGee4rAAkWuC8Ei1TI\n' +
            '8AhBQvrpmc92O9elc1gNIwARAQABiQIfBBgBCAAJBQJZDiNcAhsMAAoJEIUFLWkV\n' +
            'w03EljMP/RW62Jd+qsdzaHJ1qrrbF+O5vjxxJgx1kxK+in5jLPp1QFRHmr5Q4B7z\n' +
            '1m8KTQVd4vN2+ZrfQfN6YtNOSQFPKAIVeXTACswOK0Lu+b8g8MAh/L3udxD/Yai+\n' +
            'rdcpUreEAXsl2cPIfAY2wkkZBeNp8kvJDA5C/HjLogYIula9b3xOwWNwxSKmXCPT\n' +
            'oEa/ivDungyjM0iZ0DAx638+ponygFD0hhSKNkJ2+UaCTBr/LNypuz7xmEHnn2IF\n' +
            'vup+k4QdierPqkreMkEQNythdNhv9PWAAK+1IGpVXho+VSp6NM4YfxxVG2FCLTrM\n' +
            's9F9nGXYMEGjBy/iHCV+3+rbxMDRUpJ2P+4i4/GldMtoRe+eIKaLj4OJ6w+Dqt7P\n' +
            '41aw2KxEKZgFDPuiLI4qHCg4ehxlwCXRzlD+HZgLd1pc149bWWafVdQEuJD6E2FJ\n' +
            'v7oWq4/5XhqBmZFemVu5IteqcePbgPZ0zkDCdUr3qHt4mpAukkwzc7zsKErFv+vw\n' +
            'x3NtwN7GcuEko/8QvaUhbWKsVId6FipzqCzqFoB6qfOaA7PRuXqZny8EJTdbMREr\n' +
            'NKyVjh1lzXeBj/zmTYj0DO7fwWwluCsdgQ9MNTLsIzo3zC9Cl750WstFmb4ye8RJ\n' +
            'TnSyAMhfucKVPslL86xlaECViFyIyLylKN2ety63OpE8wNAaalBo\n' +
            '=Y+BI\n' +
            '-----END PGP PUBLIC KEY BLOCK-----\n';
    },


}

<template>
    <!-- press release -->
    <div class="row">
        <div class="xcol-md-12">
            <sbox cssBox="box-primary" :collapsed="true" :headerCollapse="true">
                <template slot="title">ROCA vulnerability summary</template>

                    <p>
                        Strictly - For release on October 16th at 12:00 UK, 4:00 PDT, 7:00 EDT, 22:00 AEDT
                    </p>
                    <h3>ROCA - Critical vulnerability in Infineon security chips including TPM and smart cards</h3>
                    <h4>
                        Technical details: <a target="_blank" href="https://acmccs.github.io/session-H1/">The Return
                        of Coppersmith's Attack: Practical Factorization of Widely Used
                        RSA Moduli</a> (ROCA) - ACM CCS conference on 2nd November 2017
                    </h4>

                    <p>
                        A team comprising scientists at <a target="_blank" href="https://fi.muni.cz">Masaryk University
                        (Czech Republic)</a>
                        and <a target="_blank" href="https://enigmabridge.com">Enigma Bridge Ltd (UK)</a>
                        has discovered and verified a serious vulnerability in the algorithm used to generate encryption
                        keys in an Infineon (IFX.DE) on-chip cryptographic library. This library was provided with
                        security chips manufactured from 2012, possibly earlier. Infineon, as the manufacturer of
                        impacted products, have been informed of the vulnerability in February 2017. Infineon
                        subsequently provided relevant information to their customers. The research team provided full
                        and timely support to a number of these customers. The support included further clarifications
                        of the vulnerability, expert consultations, and tools for detection of vulnerable products. All
                        this support was provided as quickly as possible and free of charge.
                    </p>

                <p>
                    This press release and referenced resources provide information needed to assess a potential impact
                    of the Return of Coppersmith Attack (ROCA) vulnerability on IT systems and applications. It follows
                    a responsible disclosure process, which we started at the beginning of February 2017 by notifying
                    the manufacturer Infineon (IFX.DE). We have reached the end of the agreed non-disclosure period,
                    which has been extended from usual 45 days to eight months due to the nature of the vulnerability.
                </p>

                <p>
                        The team will subsequently publish technical details of the security vulnerability at the ACM
                        CCS 2017, a computer security conference, on 2nd November 2017 in Dallas, USA under the
                        title: <i>The Return of Coppersmith's Attack: Practical Factorization of Widely Used RSA Moduli.
                    </i>
                </p>

                <p>
                        The responsible disclosure framework is a multi-stage process to prevent third parties gaining
                        security, financial, or other advantage over users of products vulnerable to cyber attacks.
                        These best practices have been established to protect users by ensuring that security
                        vulnerabilities are corrected in a timely and orderly fashion and they are widely accepted by
                        the IT industry, and government bodies (CERT, CSIRT, OWASP, NIST, etc.). Publishing
                        vulnerabilities helps identify their causes and improves the overall security of technology
                        users.
                </p>

                <p><b>Vulnerability</b></p>

                <p>
                        The ROCA vulnerability enables computation of RSA private keys from their public components for
                        a range of key lengths, including commonly used 2,048 bits with low to medium budget. A
                        successful computation of a private key allows, depending on its use, decrypt sensitive data
                        (from file encryption to HTTPS), forging digital signatures (email security, qualified
                        signatures), impersonation (access control to IT systems or buildings), or personal identity
                        theft (e-ID cards).
                    </p>

                    <p>
                        The current indicative processor times for 1,024 and 2,048 bit keys are as follows.
                    </p>
                    <ul>
                        <li>1,024 bit RSA keys – 97 vCPU days (maximum cost of $40-$80); and</li>
                        <li>2,048 bit RSA keys – 51,400 vCPU days, (maximum cost of $20,000 - $40,000).</li>
                    </ul>
                    <p>
                        Computations can be split among an arbitrary number of processors and completed in days, hours,
                        or even minutes. These time and cost estimates were valid at the time of publication and may
                        decrease.
                    </p>

                <p>
                    The fault is present in the Infineon cryptographic library RSA v1.02.013 and subsequent versions. We
                    have identified the following products using this library:
                </p>
                <ul>
                    <li>smart cards with Infineon processors, including SLE77, SLE78 – note that not all products
                        make use of the affected library;</li>
                    <li>Infineon TPM modules – these represent around 25-30% of all TPM modules globally and are used in
                        a number of PC and laptop brands including Lenovo, HP, Dell, Microsoft, or Asus; and</li>
                    <li>third-party hardware authentication tokens utilizing affected processors (Yubikey 4). </li>
                </ul>


                <p><b>Impact</b></p>

                    <p>
                        The vulnerability impacts applications, which use the faulty on-chip key generation library. A
                        large number of governments (including EU-wide qualified electronic signature schemes),
                        enterprise and organizational applications may be affected and we encourage organizations to
                        activate their security incident response plans to assess their exposure, potential impact, and
                        mitigate relevant risks.
                    </p>

                    <p>
                        Some large deployments of these chips are NOT impacted. Use cases, where RSA keys are generated
                        outside the security chips, e.g., EMV (chip & PIN and chip & signature) banking cards, or
                        passports should not be affected. The vulnerability has also a very limited impact on the
                        internet security (HTTPS, SSL/TLS, etc.).
                    </p>

                    <p>
                        RSA is a widely used encryption and signing algorithm for data encryption, digital signing, user
                        and device authentication. It also provides citizen identification for some e-Government
                        services. The library in question has been used in a range of products including smart cards,
                        trusted platform modules (TPM), and hardware security tokens (e.g., Yubikey). These products
                        are used to provide an additional security for users as well as laptops and computers. The
                        security of some cloud applications may also depend on TPM modules.
                    </p>

                    <p>
                        As the affected products are also used to provide a high-level physical security, the
                        vulnerability may impact authentication cards and tokens for physical access to high-security
                        buildings, authentication to critical information systems, EU qualified electronic signatures,
                        or even national health and ID schemes.
                    </p>

                    <p>
                        Using our limited resources and access, we have confirmed around 760,000 keys from a range of
                        devices to be vulnerable. We expect the overall number of vulnerable devices to be in the order
                        of tens of millions or more.
                    </p>

                    <p><b>Assessment, mitigation</b></p>

                <p>
                    The vulnerability can’t be used for large-scale attacks but there is a practical method for targeted
                    attacks.
                </p>
                <p>
                    Please contact manufacturers of products you suspect may be affected, or check their latest bug and
                    press releases, and product updates for more information.
                </p>
                    <p>
                        If you suspect you or your organization's security may be at risk, we have implemented a tool to
                        test RSA public keys for the ROCA vulnerability. It is available for download and in online test
                        toolkits at these web addresses:
                    </p>
                    <ul>
                        <li><a href="https://rocahelp.com">https://rocahelp.com</a> (or <a href="https://keychest.net/roca">https://keychest.net/roca</a>)</li>
                        <li><a target="_blank" href="https://keytester.cryptosense.com">https://keytester.cryptosense.com</a></li>
                    </ul>

                    <p>
                        A list of possible mitigation actions includes:
                    </p>
                    <ul>
                        <li>install security updates provided by manufacturers if available;</li>
                        <li>replacement of security chips / products with these chips with secure ones;</li>
                        <li>change the source of RSA keys to a secure key generator;</li>
                        <li>replace RSA algorithm with an elliptic curve encryption (ECC);</li>
                        <li>shorten the lifetime of RSA keys;</li>
                        <li>limit access to repositories with public keys; or</li>
                        <li>separation of data-at-rest and data-in-transit encryption.</li>
                    </ul>

                    <p>-------- end of the text -------- </p>

                <p>Tereza Fojtova<br/>
                Communication Director<br/>
                Masaryk University<br/>
                Phone: +420 724 517 335<br/>
                Email: press@rect.muni.cz<br/>
                Website: <a target="_blank" href="https://crocs.fi.muni.cz/public/blog/main">https://crocs.fi.muni.cz/public/blog/main</a>
                </p>


                <p>
                    <b>The Faculty of Informatics at Masaryk University</b> was established in 1994 and is the very
                    <a target="_blank" href="https://fi.muni.cz">first specialized faculty of its kind in the Czech Republic</a>. Its
                    establishment followed decades
                        of experience built in the area of Mathematical Informatics at the Faculty of Science, and with
                        significant involvement from prominent specialists in the field of informatics at both national
                        and international levels. A high standard of teaching is combined with in depth scientific
                        research in many areas of informatics, such as Quantum Informatics, Concurrent and Distributed
                        Computing or Computer and Communication Security.
                    </p>

                    <p>
                        <b>Enigma Bridge Ltd</b> is a technology start-up providing cloud encryption and key management
                        services. Its <a href="https://keychest.net">KeyChest key management platform</a> provides a
                        simple certificate expiry monitoring
                        with automatic key enrolment and discovery. Its certificate renewal capabilities significantly
                        reduce labour and management cost. It uses a unique secure hardware encryption platform for
                        physical security of customer secrets and encryption keys. This hardware platform uses large
                        numbers of secure processors to provide scalability, as well as secure remote operations.
                    </p>
            </sbox>
        </div>
    </div>
</template>
<script>
    export default {

    }
</script>
<style scoped>

</style>
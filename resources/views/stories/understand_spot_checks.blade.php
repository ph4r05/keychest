@extends('layouts.stories')

@section('content-nav')
@endsection

@section('story')

    <!-- bloc-5 -->
    <div class="bloc bgc-white tc-onyx l-bloc" id="bloc-5">
        <div class="container bloc-sm">
            <div class="row">
                <div class="col-sm-12">
                    <h1 class="mg-md  tc-rich-electric-blue">
                        Understand spot checks
                    </h1>
                    <p class=" mg-lg">
                        We have implemented spot checks so that they return as little information as necessary. The main
                        point was simplicity. If there is a problem that needs to be fixed, you will see it. If you get
                        everything right, you will get thumbs up. When you start using it, however, you may
                        wonder why you are shown certain information. This page explains what we mean, when we show you
                        certain information to re-assure you about the results of your tests.
                    </p>
                    <h2 class="mg-md  tc-rich-electric-blue">
                        Which server you test
                    </h2>
                    <p>
                        KeyChest checks servers with names you give it. It resolves the name, gets an IP address and
                        runs tests against that IP address. If the domain name leads
                        to, e.g., a web-server, which then redirects your browser, we will ignore this redirection. The
                        initial scanning will be done against the “primary” server. If we detect a re-direction,
                        you will see a text allowing you to do a one-click scan of the secondary server.
                    </p>
                    <h2 class="mg-md  tc-rich-electric-blue">
                        Time validity messages
                    </h2>
                    <p>
                        We show four different messages according to how much time is left till the certificate expires.<br>
                    </p>
                    <ul>
                        <li>
                            <p>
                                OK - there is more than 28 days left till the certificate expiration date;
                            </p>
                        </li>
                        <li>
                            <p>
                                PLAN - if the time left is between 2 and 28 days;
                            </p>
                        </li>
                        <li>
                            <p>
                                WARNING - if there&rsquo;s less than 48 hours left before your server becomes
                                unavailable;
                            </p>
                        </li>
                        <li>
                            <p>
                                ERROR - your server is not available to your customers and clients as the certificate
                                has expired.
                            </p>
                        </li>
                    </ul>

                    <h2 class="mg-md tc-rich-electric-blue">Server downtime</h2>
                    <p>
                        KeyChest measures downtime over past 2 years and it uses two sources of information:
                    </p>
                    <ul>
                        <li>certificate transparency (CT) logs of issued certificates; and</li>
                        <li>actual scans of your servers, if there are any.</li>
                    </ul>

                    At the moment, the downtime is shown as a percentage of the 2 year period, as well as in absolute hours/days.
                    </p>
                    <h2 class="mg-md tc-rich-electric-blue">Trust chain</h2>
                    <p>
                        Each server should send all certificates a client needs to verify the server's security. It usually
                        involves a certificate of the server, one or more issuing certification authorities (CAs), and a root
                        certificate.
                        </p>
                    <p>
                        The root certificate has to be installed as trusted on your computer. If it is not, your browser
                        will show the server as untrusted and potentially dangerous.
                    </p>
                    <h2 class="mg-md tc-rich-electric-blue">Certificate issuer</h2>
                    <p>
                        We now show the organization name of the authority, which issued your server's certificate, for example:
                        <b>enigmabridge.com (by Cloudflare)</b>.  Simple as that.
                    </p>
                    <h2 class="mg-md tc-rich-electric-blue">Servers in SAN certificates (i.e., neighbors)</h2>
                    <p>
                        It is sometimes useful to have one certificate for all (logical) servers running on the same
                        hardware. These certificates are called SAN (Subject Alternative Names) certificates, or
                        Unified Communications Certificates (UCC), or Multi Domain certificates.
                    </p>
                    <p>
                        SAN certificates are different from "wildcard" certificates. SAN certificates list all server
                        names that can use it. Wildcard certificates can be used by any user, which has a particular
                        common domain name, e.g., *.mythic-beasts.com can be used for dan.mythic-beasts.com,
                        test1.internal.mythic-beasts.com, etc., but not for "mythic-beasts.com" as it doesn't contain the
                        leading dot.
                    </p>
                    <p>
                        If the server uses a SAN certificate, we will show all alternative names, which you can quickly
                        test simply by clicking their names in the list.
                    </p>
                    <h2 class="mg-md tc-rich-electric-blue">Hostname match</h2>
                    <p>
                        Obviously, servers have to use certificates, which contain their name. Otherwise we could all use
                        one universal certificate and all the security would be gone. If there is a problem, the spot check
                        will show a warning and a list of names, which are in the certificate.
                    </p>
                    <p>
                        We usually see this problem on servers, which have a default certificate provided by their content
                        delivery network (CDN) for internal use, or when users configure their server(s) with wrong
                        certificate files.
                    </p>
                    <h2 class="mg-md tc-rich-electric-blue">SSL detection</h2>
                    <p>
                        While we still keep talking about SSL security, the SSL protocol itself has been shown to be broken.
                        There is a replacement, widely supported, protocol called TLS, which all web servers should use these days.
                    </p>
                    <p>
                        If you still use the SSL protocol, whether it's version 2 or 3, we will flag it as a problem during
                        our server scanning.
                    </p>
                    <h2 class="mg-md tc-rich-electric-blue">HTTP Strict Transport Security</h2>
                    <p>
                        One of the problems with SSL protocols were attacks downgrading HTTPS to HTTP. Users should notice it,
                        but we are not trained for that, and we often don't notice until it is too late.
                    </p>
                        HTTP Strict Transport Security (HSTS) is a piece of information from web servers, which tells your
                    browser that it intends to always use HTTPS. Your web browsers will store this information and reject
                    any subsequent insecure connections to such servers.
                    </p>


                </div>
            </div>
        </div>
    </div>
    <!-- bloc-5 END -->
@endsection

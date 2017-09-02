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
                    <p class=" mg-md">
                        The main goal of KeyChest Spot Check is to give you a quick information about the configuration
                        of a particular server. We tried to make it as simple as possible and it reflects in the amount
                        of information you can see. <br><br>

                        If the server in question is well configured, you will see just a
                        "well done" message. If there is a problem that needs to be fixed, you will see everthing relevant
                        to the problem. The downside is that when you start using it, you may
                        wonder why you are shown only so little information. <br><br>

                        This page explains what KeyChest Spot Check does so you know what to expect.
                    </p>
                    <h2 class="mg-md  tc-rich-electric-blue">
                        Which server you test
                    </h2>
                    <p class=" mg-lg">
                        KeyChest checks servers with names you give it. It resolves the name, gets an IP address and
                        runs tests against that IP address. If the domain name leads
                        to, e.g., a web-server, which then redirects your browser, KeyChest ignores this redirection. The
                        initial scanning will be done against the specified server. If we detect a re-direction,
                        you will see a text allowing you to do a one-click scan of the secondary server.<br><br>

                        If KeyChest detects more IP addresses for a given domain name, it will automatically scan
                        one of the IP addresses and shows a list of all other detected IP addresses. Simply click any
                        of those to test a particular IP address you're interested in. <br><br>

                        In some cases, the certificate used shows additional servers, as it may be needed for load balancing,
                        or high availability reasons. If it is the case, you will see all those names and can test them
                        one by one as well.

                    </p>
                    <h2 class="mg-md  tc-rich-electric-blue">
                        Time validity messages
                    </h2>
                    <p class=" mg-sm">
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
                    <p class=" mg-sm">
                        KeyChest measures downtime over past 2 years and it uses two sources of information:
                    </p>
                    <ul>
                        <li>certificate transparency (CT) logs of issued certificates; and</li>
                        <li>actual scans of your servers, if there are any.</li>
                    </ul>

                    At the moment, the downtime is shown as a percentage of the 2 year period, as well as in absolute hours/days.
                    </p>
                    <h2 class="mg-md tc-rich-electric-blue">Trust chain</h2>
                    <p class=" mg-md">
                        Each server should send all certificates a client needs to verify the server's security. It usually
                        involves a certificate of the server, one or more issuing certification authorities (CAs), and a root
                        certificate.
                        </p>
                    <p class=" mg-md">
                        The root certificate has to be installed as trusted on your computer. If it is not, your browser
                        will show the server as untrusted and potentially dangerous.
                    </p>
                    <h2 class="mg-md tc-rich-electric-blue">Certificate issuer</h2>
                    <p class=" mg-md">
                        We now show the organization name of the authority, which issued your server's certificate, for example:
                        <b>enigmabridge.com (by Cloudflare)</b>.  Simple as that.
                    </p>
                    <h2 class="mg-md tc-rich-electric-blue">Servers in SAN certificates (i.e., neighbors)</h2>
                    <p class=" mg-md">
                        It is sometimes useful to have one certificate for all (logical) servers running on the same
                        hardware. These certificates are called SAN (Subject Alternative Names) certificates, or
                        Unified Communications Certificates (UCC), or Multi Domain certificates.
                    </p>
                    <p class=" mg-md">
                        SAN certificates are different from "wildcard" certificates. SAN certificates list all server
                        names that can use it. Wildcard certificates can be used by any user, which has a particular
                        common domain name, e.g., *.mythic-beasts.com can be used for dan.mythic-beasts.com,
                        test1.internal.mythic-beasts.com, etc., but not for "mythic-beasts.com" as it doesn't contain the
                        leading dot.
                    </p>
                    <p class=" mg-md">
                        If the server uses a SAN certificate, we will show all alternative names, which you can quickly
                        test simply by clicking their names in the list.
                    </p>
                    <h2 class="mg-md tc-rich-electric-blue">Hostname match</h2>
                    <p class=" mg-md">
                        Obviously, servers have to use certificates, which contain their name. Otherwise we could all use
                        one universal certificate and all the security would be gone. If there is a problem, the spot check
                        will show a warning and a list of names, which are in the certificate.
                    </p>
                    <p class=" mg-md">
                        We usually see this problem on servers, which have a default certificate provided by their content
                        delivery network (CDN) for internal use, or when users configure their server(s) with wrong
                        certificate files.
                    </p>
                    <h2 class="mg-md tc-rich-electric-blue">SSL detection</h2>
                    <p class=" mg-md">
                        While we still keep talking about SSL security, the SSL protocol itself has been shown to be broken.
                        There is a replacement, widely supported, protocol called TLS, which all web servers should use these days.
                    </p>
                    <p class=" mg-md">
                        If you still use the SSL protocol, whether it's version 2 or 3, we will flag it as a problem during
                        our server scanning.
                    </p>
                    <h2 class="mg-md tc-rich-electric-blue">HTTP Strict Transport Security</h2>
                    <p class=" mg-md">
                        One of the problems with SSL protocols were attacks downgrading HTTPS to HTTP. Users should notice it,
                        but we are not trained for that, and we often don't notice until it is too late.
                    </p>
                    <p class=" mg-md">
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

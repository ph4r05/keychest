<help-modal id="scanHelpModal" title="Spot check">
    <p>
        Spot check is a powerful tool for a quick assessment of the SSL/TLS configuration of your servers.
    </p>
    <p>
        It resolves the name of a server you provide and runs a series of tests against one of the IP addresses.
        It does not follow HTTP redirects, but it shows if one is in place so you can follow the link manually.  If
        there are more IP addresses you can see the list and use it to check a particular IP address, if appropriate.
    </p>
    <p>
        The list of spot check tests:
    </p>
    <ul>
        <li>DNS configuration - resolving IP addresses from your server name;</li>
        <li>Server detection - warning if ther is no server at all listening at the given server and port;</li>
        <li>SSL detection - if your server uses insecure version SSL2 or SSL3, it will be displayed (see errors below);</li>
        <li>certificate expiration - how many days till the certificate expires;</li>
        <li>downtime - downtime during the last 2 years; CT logs data amended with server checks if this data is
            available;</li>
        <li>trust chain - whether the server provides a complete chain of certificates needed
            for validation;</li>
        <li>certificate issuer - it shows the name of the certificate issuer (if set);</li>
        <li>list of neighbors - the list of all names in the certificate;</li>
        <li>hostname match - whether the name(s) in the certificate contain the server's name;</li>
        <li>HSTS - if the HSTS (HTTP Strict Server Security) is enabled; </li>
        <li>HTTP redirection - an active redirection, which sends web browsers to another server; </li>
        <li>IPv6 configuration - we start checking IPv6 addresses, if available, this may be of interest for successful
        deployment of Let's Encrypt certificates; and</li>
        <li>IP addresses - a list of all IP addresses available in the KeyChest's geographic region.</li>
    </ul>

    <p>Possible errors returned by the TLS/HTTPS scanner are:</p>
    <ul>
        <li>Domain lookup error - we can't get a valid IP address.</li>
        <li>Connection error - no server listening on the server and port given.</li>
        <li>Timeout - no response from the server, often due to a firewall protection.</li>
        <li>No TLS/HTTPS server found - a server detected, but it doesn't use SSL/TLS.</li>
        <li>TLS handshake error - error during a TLS handshake - possible an insecure version (SSL2, or SSL3).</li>
    </ul>

    <p>
        If you are not sure the results you can see are correct, or have any other question, please let us know at
        <a class="tc-rich-electric-blue" href="mailto:support@enigmabridge.com">support @ enigmabridge.com</a> or use
        a support form to get in touch.
    </p>
</help-modal>

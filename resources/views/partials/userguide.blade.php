<h3 class="mg-md">Welcome to KeyChest</h3>

<p>
    We have designed KeyChest as we need to continuously monitor our servers to avoid any downtimes
    due to expire or misconfigured certificates. We implemented it as a system you setup and forget. It just
    works.
</p>

<p>
    We had a pretty good idea about the main features to make KeyChest a real helping tool, rather than just yet
    another management system we have to think about all the time (did we add all servers, does data
    collection work, etc.). The best monitoring system is useless, if you forget to register your
    servers with it. So KeyChest will automatically find all new servers from <i>Active Domains</i> the moment
    you prime them with their first certificate. It will keep monitoring servers to detect any downtimes
    and it will only bother you if there is a problem.
</p>

<h3 class="mg-md">New server - recommended approach</h3>
<p>
    Setting up a new server is usually a fun - so long as there aren't too many of them. Still, it's
    good to choose and follow the same procedure to get it done without wasting our time. We
    recommend the following approach with a clear handover from <i>setting up</i> (project)
    to <i>keep it running</i> (operations).
</p>
<p><b>Option 1</b></p>

<ul>
    <li>1. Setup / amend the configuration of your server.</li>
    <li>2. Use our <a href="/home/scan">Spot Check</a> to verify that your configuration is
        correct and repeat step 1 as necessary. </li>
    <li>3. (Optional, but useful) Use <a href="https://www.ssllabs.com/ssltest/" target="_blank"> SSLLabs</a> to
        get detailed information about cryptographic security and adjust your configuration.</li>
    <li>4. <a href="/home/servers">Start tracking</a> the server by adding here to your list of servers.</li>
    <li>5. All is good, the configuration process is completed. Let's handover to "ops".</li>
    <li>6. Check regular server status updates from KeyChest to detect renewal failures or to plan
    manual renewals as appropriate.</li>
</ul>

<p><b>Option 2 - Watch Now&trade;</b></p>
<ul>
    <li>1. Use <a href="/home/servers">Active Domains</a> to add your registered domain or sub-domain
    for automatic server discovery. Switch on the Watch Now&trade; option.</li>
    <li>2. When you/someone creates a certificate for a new server, KeyChest will start monitoring it.</li>
    <li>3. If the next status update shows configuration problems with the server, use
        <a href="/home/scan">Spot Check</a> to quickly check your corrections.</li>
</ul>



<h3 class="mg-md">How to use KeyChest</h3>

<p>
    KeyChest gives you a quick information when you are setting up a new server and it
    continuously monitors your certificates and servers. It is not intended for thorough
    security audits as there are already good free tools for that. Also, each thorough
    security audit takes several minutes to complete and you really want to run
    those only when the basics are right (server is running, firewall open, TLS setup,
    certificate installed, etc.). KeyChest helps to quickly complete and verify this initial
    configuration.
</p>

<p>
    KeyChest consists of two main tools:
<ul>
    <li>Spot check - a quick check of a particular server with instant results. A typical
        use-case for spot check is setup of a new server, or to check the correct setup after
        configuration changes.
    </li>
    <li>Dashboard - this tool provides a concise but easy to understand overview of the status
        of all watched servers or internet domains.
    </li>
</ul>


If you want to monitor, you need to import some data into a monitoring system first. There are three options
to add new servers into KeyChest and some more options, if you deploy it on-premise:

<ul>
    <li>Serve name - you simply enter a server name you want to start watching.</li>
    <li>Active domain - you add a domain, for which you want to watch all the subdomains.</li>
    <li>Bulk import - you can paste a list of domain names - one per line, to speed up initial import.</li>
</ul>


On-premise deployments and our enterprise version can also scan ranges of private IP addresses with standalone agents.
</p>

<h3 class="mg-md">Watch Now&trade;</h3>

Watch Now&trade; is a feature you can switch on when you set new Active Domains. It really makes
KeyChest a powerful monitoring system, which thinks for you. You don't have to think about
monitoring when you set up a new server. The Watch Now&trade; feature will detect the new server is
using a certificate and it will start watching it automatically.

That means it will appear in the next status update and it will show any configuration problem you
may have missed.


<h3 class="mg-md">Technical specification</h3>
<ul>
    <li>Maximum number of monitored servers - UNLIMITED.</li>
    <li>Maximum number of Active Domains - UNLIMITED.</li>
    <li>User identification - via email address, regardless of the login/registration method.</li>
    <li>Organization - not available.</li>
    <li>User / role definitions - not available.</li>
    <li>User identification - by email; you can use any login method and so long as it provides the same
        email address you will access your account.</li>
    <li>Watch Now&trade; interval - 48 hours.</li>
    <li>Server check interval - 2 hours.</li>
    <li>DNS check interval - 10 minutes.</li>
    <li>whois check interval - 48 hours.</li>
    <li>Server new certificate detection interval - 2 hours.</li>
</ul>

<h3 class="mg-md">Server configuration errors</h3>

<p>
    The list of possible error causes shown in the section <i>Servers with configuration errors</i> includes:
</p>
<ul>
    <li>Self-signed certificate - the certificate provided by the server is self-signed; this
    can be due to a path to a default certificate still present in the server configuration;</li>
    <li>CA certificate - the server's certificate has a "CA flag" set; this means the related
        key can sign other certificates. This is forbidden.</li>
    <li>Validation failed - there was an unspecified error when we tried to validate the server's
    certificate.</li>
    <li>Incomplete trust chain - there is a certificate missing in the trust chain provided by
    the server. Check that the certificate "bundle"/intermediate certificates are correctly defined.</li>
    <li>No certificate - the server didn't provide any certificate.</li>
    <li>Untrusted certificate - the certificate is not trusted. You may be using your own internal
    CA.</li>
</ul>

<h3 class="mg-md">TLS errors in Spot Check</h3>

<p>
    The list of possible error causes shown by <i>Spot Check</i> includes:
</p>
<ul>
    <li>TLS handshake error - check your TLS configuration, this error may indicate use of
    insecure versions SSL3 or SSL2. The same error can be caused by StartTLS, which we do
    not support yet;</li>
    <li>Connection error - we couldn't connect to the server, check that the port is correct and/or
    the server is running; and</li>
    <li>Timeout - there was no response to TLS requests; check whether the port is open in your
    firewall(s).</li>
</ul>


<h3 class="mg-md">Current Restrictions</h3>

<p>
    The system is currently under intensive development and we add features as they are requested by
    you, our users. This section lists some of the restrictions we are aware of and will improve or completely
    remove.
</p>
<ul>
    <li>IP address scanning - we test the first resolved IP address for a given server
        name. This may give incomplete picture, e.g., when you use some kind of DNS rotation / load balancing.
    </li>
    <li>One certificate per server - we perform TLS handshake to receive a server certificate, some
        servers use two or more certificates, depending on the browser ciphersuite set. Typically a web
        server would have an RSA and ECC certificate.
    </li>
    <li>SSL only - we do not support StartTLS so you can only test configuration of email servers
        (IMAP, POP, SMTP) with SSL.
    </li>
    <li>
        Instant server/active domain removal - removes servers without any visible trace. It makes sense
        to keep related certificates in the monitoring Dashboard for a few weeks, marked is "legacy".
    </li>
    <li>
        Time shift by one hour - this is a bug that affects/affected some database entries. Timestamps of
        events are shown as happening one hour earlier than they should be.
    </li>
</ul>

<h3 class="mg-md">Enterprise Features</h3>

<p><i>We expect launch of the enterprise version in August.</i></p>

<p>
    We have started building a set of enterprise features, for users who want to manage
    certificates in a large company or a corporation. The main differences from the free
    version is organization/user management, and monitoring of internal networks.
</p>
<p>
    User and organization management provides separation of servers and certificates relevant
    to a particular department. The management structure is defined at two levels of granularity:
</p>
<ul>
    <li>departments - organizational units within a company, which are responsible for particular
        networks or servers; and</li>
    <li>users - users responsible for relevant infrastructure operations within their department.</li>
</ul>
<p>
    The second feature of the enterprise version is monitoring of internal network servers. This is
    being implemented via lightweight monitoring agents scanning and testing servers from within
    their VLAN.
</p>
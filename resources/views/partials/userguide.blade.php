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
    servers with it. So KeyChest will automatically find all new servers from "Active Domains" the moment
    you prime them with their first certificate. It will keep monitoring servers to detect any downtimes
    and it will only bother you if there is a problem.
</p>

<h3 class="mg-md">New server - recommended approach</h3>
<p>
    Setting up a new server is usually fun - so long as there aren't too many of them. We recommend
    the following approach with a clear "hand-over" from "setting up" to "operations".
</p>
<p><b>Option 1</b></p>

<ul>
    <ol>Setup / amend configuration of your server.</ol>
    <ol>Use our <a href="/home/scan">Spot Check</a> to verify that your configuration is
        correct and repeat step 1 as necessary. </ol>
    <ol>(Optional, but useful) Use <a href="https://www.ssllabs.com/ssltest/" target="_blank"> SSLLabs</a> to
        get detailed information about cryptographic security and adjust your configuration.</ol>
    <ol><a href="/home/servers">Start tracking</a> the server by adding here to your list of servers.</ol>
    <ol>All is good, the configuration process is completed.</ol>
    <ol>Check regular server status updates from KeyChest to detect renewal failures or to plan
    manual renewals as appropriate.</ol>
</ul>

<p><b>Option 2 - Watch Now&trade;</b></p>
<ul>
    <ol>Use <a href="/home/servers">Active Domains</a> to add your registered domain or sub-domain
    for automatic server discovery. Switch on the Watch Now&trade; option.</ol>
    <ol>When you/someone creates a certificate for a new server, KeyChest will start monitoring it.</ol>
    <ol>If the next status update shows configuration problems with the server, use
        <a href="/home/scan">Spot Check</a> to quickly check your corrections.</ol>
</ul>



<h3 class="mg-md">How to use KeyChest</h3>

<p>
    KeyChest gives you a quick information when you are setting up a new server and it continuously monitors
    your certificates and servers. It is not intended for thorough security audits as each would take several
    minutes to complete and you really want to run those only when the basics are right (server is running,
    firewall open, TLS setup, certificate installed, etc.). KeyChest helps to quickly complete and verify the
    initial configuration.
</p>

<p>
    KeyChest consists of two main tools:
<ul>
    <li>Spot check - a quick check of a particular server with instant results. A typical use-case for spot check
        is setup of a new server, or to check the correct setup after configuration changes.
    </li>
    <li>Dashboard - this tool provides a concise but easy to understand overview of the status of all "watched"
        servers or internet domains.
    </li>
</ul>

Initially, we thought that the Spot Check would be used first - to help you with configuration - and Dashboard
will take over with its continuous monitoring. The flow will change, however, when you use Active Domains with
the Watch Now&trade; feature.

If you want to monitor, you need to import some data into a monitoring system first. There are three options
to add new servers into KeyChest and some more options, if you deploy it on-premise:

<ul>
    <li>Serve name - you simply enter a server name you want to start watching.</li>
    <li>Active domain - you add a domain, for which you want to watch all the subdomains.</li>
    <li>Bulk import - you can paste a list of domain names - one per line, to speed up initial import.</li>
</ul>

On-premise deployments and our enterprise version can also scan ranges of private IP addresses with standalone agents.
</p>


<h3 class="mg-md">Technical specification</h3>
<ul>
    <li>Maximum number of monitored servers - UNLIMITED</li>
    <li>Maximum number of Active Domains - UNLIMITED</li>
    <li>User identification - via email address, regardless of the login/registration method.</li>
    <li>Organization</li>
    <li></li>
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

<p>
    We have started building a set of enterprise features, if you want to manage certificates of a
    large company or a corporation. The main difference from the free version is organization and user
    management. User management is defined in two levels:
</p>
<ul>
    <li>departments - organizational units within a company; and</li>
    <li>users - users within a department.</li>
</ul>
<p>
    The second feature of the enterprise version is monitoring of internal network servers. This is
    being implemented via lightweight monitoring agents scanning and testing servers from within
    their VLAN.
</p>


<h3 class="mg-md">Welcome to KeyChest</h3>

<p>
    We have designed KeyChest as we need to continuously monitor our servers to avoid any downtimes
due to expired or misconfigured certificates. We implemented it as a system you setup and forget. It just
works.
</p>

<p>
We had a pretty firm idea about main features to make it a real helping tool, rather than just yet
    another management system we have to think about all the time (did we add all servers, does data
    collection work, etc.). The best monitoring system is useless, if you forget to register your
servers with it. So KeyChest will automatically find all new servers from "Responsive Domains" the moment
    you prime them with their first certificate. It will keep monitoring servers to detect any downtimes
    and it will only bother you if there is a problem.
</p>


<h3 class="mg-md">How to use KeyChest</h3>

<p>
    KeyChest gives you a quick information when you are setting up a new server and it continously monitors
    your certificates and servers. It is not intended for thorough security audits as each would take several
    minutes to complete and you really want to run those only when the basics are right (server is running,
    firewall open, TLS setup, certificate installed, etc.). KeyChest helps to quickly complete and verify the
    initial configuration.
</p>

<p>
    KeyChest consists of two main tools:
    <ul>
    <li>Spot check - a quick check of a particular server with instant results. A typical use-case for spot check
    is setup of a new server, or to check correct setup after configuration changes.</li>
    <li>Dashboard - this tool provides a concise but easy to understand overview of the status of all "watched"
    servers or internet domains.</li>
</ul>

Initially, we thought that Spot checks would be used first - to help you with configuration - and Dashboard
will take over with its continuous monitoring. The flow changes when you use "Responsive Domains".

If you want to monitor, you need to import some data into a monitoring system first. There are three options
to add new servers into KeyChest and some more options, if you deploy it on-premise:

    <ul>
        <li>Serve name - you simply enter a server name you want to start watching.</li>
        <li>Responsive domain - you add a domain, for which you want to watch all the subdomains.</li>
        <li>Bulk import - you can paste a list of domain names - one per line, to speed up initial import.</li>
    </ul>

On-premise deployments can also scan ranges of private IP addresses.
</p>



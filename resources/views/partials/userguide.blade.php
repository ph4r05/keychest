<div class="nav-tabs-custom">
   <ul class="nav nav-tabs">
      <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Introduction</a></li>
      <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">New Server</a></li>
      <li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">Fair Use</a></li>
      <li class=""><a href="#tab_4" data-toggle="tab" aria-expanded="false">Restrictions</a></li>
      <li class=""><a href="#tab_5" data-toggle="tab" aria-expanded="false">Error Codes</a></li>
      <li class=""><a href="#tab_6" data-toggle="tab" aria-expanded="false">KeyChest Versions</a></li>
      <li class="pull-right"><a href="#" class="text-muted"><i class="fa fa-gear"></i></a></li>
   </ul>
   <div class="tab-content">
      <div class="tab-pane active" id="tab_1">
         <p class="tc-onyx">
            We have designed KeyChest as we needed to continuously monitor our servers to avoid downtimes
            due to expire or misconfigured certificates. We implemented it as a system you would set up and forget.
             A system that just works. We had a pretty good idea about the main features to make KeyChest a useful tool,
             rather than just yet-another-management-system we have to think about all the time (did we add all servers,
             does data collection work, etc.).
         </p>
          <p class="tc-onyx">
            The best monitoring system is useless, if you forget to register your
            servers with it. So KeyChest will automatically find all new servers from <i>Active Domains</i> the moment
            you get their first certificate. KeyChest will keep monitoring all servers to detect any downtimes
            and it will only bother you if there is a problem.
         </p>
         <h3 class="mg-md tc-rich-electric-blue">How to use KeyChest</h3>
          <p class="tc-onyx">
            KeyChest gives you a quick information when you are setting up a new server and it
            continuously monitors your certificates and servers. It is not intended for thorough
            security audits as there are already good free tools for that. Also, each thorough
            security audit takes several minutes to complete and you really want to run
            those only when the basics are right (server is running, firewall open, TLS setup,
            certificate installed, etc.). KeyChest helps to quickly complete and verify this initial
            configuration.
         </p>
          <p class="tc-onyx">
            KeyChest consists of two main tools:
          <ul class="tc-onyx">
            <li>Spot check - a quick check of a particular server with instant results. A typical
               use-case for spot check is setup of a new server, or to check the correct setup after
               configuration changes.
            </li>
            <li>Dashboard - this tool provides a concise but easy to understand overview of the status
               of all watched servers or internet domains.
            </li>
         </ul>
          <p class="tc-onyx">
              If you want to monitor, you need to import some data into a monitoring system first. There are three options
         to add new servers into KeyChest and some more options, if you deploy it on-premise:
          </p>
          <ul class="tc-onyx">
            <li>Serve name - you simply enter a server name you want to start watching.</li>
            <li>Active domain - you add a domain, for which you want to watch all the subdomains.</li>
            <li>Bulk import - you can paste a list of domain names - one per line, to speed up initial import.</li>
         </ul>
          <p class="tc-onyx">
          On-premise deployments and our enterprise version can also scan ranges of private IP addresses with standalone agents.
         </p>
      </div>
      <!-- /.tab-pane -->
      <div class="tab-pane" id="tab_2">
         <h3 class="mg-md tc-rich-electric-blue">New server - recommended approach</h3>
          <p class="tc-onyx">
            Setting up a new server is usually a fun - so long as there aren't too many of them. Still, it's
            good to choose and follow the same procedure to get it done without wasting our time. We
            recommend the following approach with a clear handover from <i>setting up</i> (project)
            to <i>keep it running</i> (operations).
         </p>
          <p class="tc-onyx">
              <b>Option 1</b></p>
         <ul class="tc-onyx" style="list-style: none">
            <li>1. Setup / amend the configuration of your server.</li>
            <li>2. Use our <a href="/home/scan">Spot Check</a> to verify that your configuration is
               correct and repeat step 1 as necessary.
            </li>
            <li>3. (Optional, but useful) Use <a href="https://www.ssllabs.com/ssltest/" target="_blank"> SSLLabs</a> to
               get detailed information about cryptographic security and adjust your configuration.
            </li>
            <li>4. <a href="/home/servers">Start watching</a> the server by adding here to your list of servers.</li>
            <li>5. All is good, the configuration process is completed. Let's handover to "ops".</li>
            <li>6. Check regular server status updates from KeyChest to detect renewal failures or to plan
               manual renewals as appropriate.
            </li>
         </ul>
          <p class="tc-onyx">
              <b>Option 2 - Watch Now&trade;</b></p>
         <ul class="tc-onyx" style="list-style: none">
            <li>1. Use <a href="/home/servers">Active Domains</a> to add your registered domain or sub-domain
               for automatic server discovery. Switch on the Watch Now&trade; option.
            </li>
            <li>2. When you/someone creates a certificate for a new server, KeyChest will start monitoring it.</li>
            <li>3. If the next status update shows configuration problems with the server, use
               <a href="/home/scan">Spot Check</a> to quickly check your corrections.
            </li>
         </ul>
         <h3 class="mg-md tc-rich-electric-blue">Watch Now&trade;</h3>
          <p class="tc-onyx">
          Watch Now&trade; is a feature you can switch on when you set new Active Domains. It really makes
         KeyChest a powerful monitoring system, which thinks for you. You don't have to think about
         monitoring when you set up a new server. The Watch Now&trade; feature will detect the new server is
         using a certificate and it will start watching it automatically.
         That means it will appear in the next status update and it will show any configuration problem you
         may have missed.
          </p>
      </div>
      <!-- /.tab-pane -->
      <div class="tab-pane" id="tab_3">
         <h3 class="mg-md tc-rich-electric-blue">Technical specification</h3>
          <p class="tc-onyx">
            The following list is true to the best of our knowledge. We keep updating it as quickly as we can so it
            tightly tracks changes but there will be short intervals when the list is inaccurate.
         </p>
         <ul class="tc-onyx">
            <li>Maximum number of monitored servers - 2,000 (possibly more now, get in touch if you need more).</li>
            <li>Maximum number of Active Domains - 1,000.</li>
            <li>Restrictions for server checks - TLS only; SSL2/3 show specific error, StartTLS not supported.</li>
            <li>User identification - via email address, regardless of the login/registration method.</li>
            <li>Organization management - not available.</li>
            <li>User/role definitions - not available.</li>
            <li>User identification - by email; you can use any login method and so long as it provides the same
               email address you will access your account.
            </li>
            <li>Watch Now&trade; interval - 48 hours.</li>
            <li>Server check interval - 8 hours.</li>
            <li>DNS check interval - 2 hours.</li>
            <li>whois check interval - 48 hours.</li>
            <li>Server new certificate detection interval - 12 hours.</li>
            <li>Server scanning depth - 1 IP address only.</li>
            <li>Active domain restrictions - a set of biggest websites (get in touch to lift the restriction).</li>
         </ul>
      </div>
      <!-- /.tab-pane -->
      <div class="tab-pane" id="tab_4">
         <h3 class="mg-md tc-rich-electric-blue">Current Restrictions</h3>
          <p class="tc-onyx">
            The system is currently under intensive development and we add features as they are requested by
            you, our users. This section lists some of the restrictions we are aware of and will improve or completely
            remove.
         </p>
         <ul class="tc-onyx">
            <li>IP address scanning - we now test all resolved IP addresses for a given server
               name. This may still lead to an incomplete picture, e.g., when the domain name uses some kind of DNS
               rotation / load balancing.
            </li>
            <li>One certificate per server - we perform TLS handshake to receive a server certificate, some
               servers use two or more certificates, depending on the browser ciphersuite set. In some cases, a web
               server would have an RSA and ECC certificates.
            </li>
            <li>SSL only - we do not support StartTLS so you can only test configuration of email servers
               (IMAP, POP, SMTP) with SSL.
            </li>
            <li>
               Instant server/active domain removal - removes servers without any visible trace in the user interface.
               It makes sense to keep related certificates in the monitoring Dashboard for a few weeks, marked is
               "legacy" (on the road map).
            </li>
         </ul>
      </div>
      <!-- /.tab-pane -->
      <div class="tab-pane" id="tab_5">
         <h3 class="mg-md tc-rich-electric-blue">Server configuration errors</h3>
          <p class="tc-onyx">
            The list of possible error causes shown in the section <i>Servers with configuration errors</i> includes:
         </p>
         <ul class="tc-onyx">
            <li>Self-signed certificate - the certificate provided by the server is self-signed; this
               can be due to a path to a default certificate still present in the server configuration;
            </li>
            <li>CA certificate - the server's certificate has a "CA flag" set; this means the related
               key can sign other certificates. This is forbidden.
            </li>
            <li>Validation failed - there was an unspecified error when we tried to validate the server's
               certificate.
            </li>
            <li>Incomplete trust chain - there is a certificate missing in the trust chain provided by
               the server. Check that the certificate "bundle"/intermediate certificates are correctly defined.
            </li>
            <li>No certificate - the server didn't provide any certificate.</li>
            <li>Untrusted certificate - the certificate is not trusted. You may be using your own internal
               CA.
            </li>
         </ul>
         <h3 class="mg-md tc-rich-electric-blue">TLS errors in Spot Check</h3>
          <p class="tc-onyx">
            The list of possible error causes shown by <i>Spot Check</i> includes:
         </p>
         <ul class="tc-onyx">
            <li>TLS handshake error - check your TLS configuration, this error may indicate use of
               insecure versions SSL3 or SSL2. The same error can be caused by StartTLS, which we do
               not support yet;
            </li>
            <li>Connection error - we couldn't connect to the server, check that the port is correct and/or
               the server is running; and
            </li>
            <li>Timeout - there was no response to TLS requests; check whether the port is open in your
               firewall(s).
            </li>
         </ul>
      </div>
      <!-- /.tab-pane -->
      <div class="tab-pane" id="tab_6">
         <h3 class="mg-md tc-rich-electric-blue">Enterprise Features</h3>
          <p class="tc-onyx">
              <i>We expect launch of the enterprise version in September.</i></p>
          <p class="tc-onyx">
            We have started building a set of enterprise features, for users who want to manage
            certificates in a large company or a corporation. The main differences from the free
            version is organization/user management, monitoring of internal networks, and integration.
         </p>
          <p class="tc-onyx">
            Please have a look at <a href="{{ url('/home/enterprise') }}">the Enterprise tab</a> for more details.
         </p>
      </div>
      <!-- /.tab-pane -->
   </div>
   <!-- /.tab-content -->
</div>
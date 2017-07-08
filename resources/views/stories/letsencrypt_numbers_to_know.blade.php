@extends('layouts.stories')

@section('content-nav')
@endsection

@section('story')

	<!-- bloc-4 -->
	<div class="bloc tc-onyx bgc-white l-bloc" id="bloc-4">
		<div class="container bloc-sm">
			<div class="row">
				<div class="col-sm-12">
					<h1 class="mg-md  tc-rich-electric-blue">
						Let&rsquo;s Encrypt Numbers to Know
					</h1>
					<p class=" mg-lg">
						<a class="ltc-rich-electric-blue" href="https://letsencrypt.org" target="_blank">Let's Encrypt</a>&nbsp;is now the largest certificate provider for internet facing servers (combining a Frost&amp;Sullivan report on SSL/TLS certificates from 2016 and actual data from Let's Encrypt, LE currently issues around 80% of all browser-trusted certificates). It does not issue the "most secure" certificates (i.e., EV, or extended validation certificates, which require manual validation of the address and legal status of the web service owner), but its certificates provide a very good level of security for most of us.<br><br>When we started using Let's Encrypt (LE), we slowly learnt about various limitations imposed on users. There is not any single place where you can find all important information in one place so here's the first attempt. We will amend it as we learn more directly, or from your feedback.<br>
					</p>
					<p>
						<i>Start monitoring your SSL certificates with KeyChest.net - <a class="ltc-rich-electric-blue" href="https://keychest.net" target="_blank">a free certificate dashboard and spot checker</a> (or click <a class="ltc-rich-electric-blue" href="https://keychest.net/register">"My Dashboard"</a> above).</i></p>
					</p>
					<div class="divider-h">
						<span class="divider"></span>
					</div>
					<p class="mg-lg">
						Update 30 June: a big thank you to <a class="ltc-rich-electric-blue" href="https://community.letsencrypt.org/u/schoen/summary" target="_blank">schoen</a> for his <a class="ltc-rich-electric-blue" href="https://community.letsencrypt.org/t/lets-encrypt-in-numbers-limits-restrictions-features/37113/2" target="_blank">feedback</a>, which has been now included in the text.
					</p>
					<h2 class="mg-md  tc-rich-electric-blue">
						Business restrictions
					</h2>
					<h3 class="mg-md  tc-rich-electric-blue">
						Restrictions on certificate use
					</h3>
					<p class=" mg-lg">
						Use of certificates is limited by their <strong>profile</strong>. LE offers just one certificate profile and there is no flexibility in this. The main goal is to automate certificate issuance and this is an acceptable restriction. Certificates limit the use for digital signatures and "key encipherment". From our point of view, they can be used only for:<br>1. server authentication; and<br>2. client authentication.<br><br>The good news is, you can use it for any of your internet servers - web, email, SSH, web apps, and so on.<br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Time validity
					</h3>
					<p class=" mg-lg">
						This is a property people talk about a lot. Certificates are valid for 90 days exactly. Exact means that if you get your certificate at 8:31am, it will expire 90 days later at 7:31am (certificates are backdated by 1 hour, so that servers/computers with a slightly out-of-sync clock can validate them immediately).<br><br>LE says the main reason is to encourage automation on our side. I'm not sure it quite works yet, but we are trying to chip in with some services here.<br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Multi-domain aka SAN Certificates
					</h3>
					<p class=" mg-sm">
						You can request a certificate, which will contain up to 100 domain names. The domain names map directly into names of servers, as sub-domain wildcards are not allowed.<br><br>

						The limit here is 100 domain names per certificate. Technically, it is implemented as one main “subject" distinguished name and 100 alternative names, which include the one in the subject DN.
						<br><br><i>Note: The subject distinguished name (DN) only contains a common name (CN), which is your domain name, e.g. "www.keychest.net".</i><br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						No wildcard certificates<br>
					</h3>
					<p class=" mg-lg">
						Wildcard certificates are not available. Similarly, EV (extended validation) certificates are not available, as their issuance can't be automated.<br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Supported algorithms
					</h3>
					<p class=" mg-sm">
						<a class="ltc-rich-electric-blue" href="https://letsencrypt.org/docs/integration-guide/" target="_blank">Let's Encrypt Integration Guide</a>&nbsp;states that you can request certificates for:<br>
					</p>
					<ul>
						<li>
							<p>
								RSA keys, with lengths 2048b, 3076b, or 4096b;
							</p>
						</li>
						<li>
							<p>
								P-256 ECDSA keys; and<br>
							</p>
						</li>
						<li>
							<p>
								P-384 ECDSA keys.
							</p>
						</li>
					</ul>
					<div class="divider-h">
						<span class="divider"></span>
					</div>
					<h2 class="mg-md tc-rich-electric-blue">
						Operations restrictions and requirements
					</h2>
					<h3 class="mg-md  tc-rich-electric-blue">
						Max certificate requests
					</h3>
					<p class=" mg-lg">
						If you start building a bigger network infrastructure and assign servers a public name within your company domain (e.g., enigmabridge.com), you should be aware that you can't get more than 20 certificates per week per registered domain.<br><br>If you think about a cloud service with subdomains per customer, this may be a serious problem. LE offers an opportunity to request an increase of this limit but they don't guarantee positive response or any response at all. Here's&nbsp;<a class="ltc-rich-electric-blue" href="https://docs.google.com/forms/d/e/1FAIpQLSfg56b_wLmUN7n-WWhbwReE11YoHXs_fpJyZcEjDaR69Q-kJQ/viewform?c=0&amp;w=1" target="_blank">the address of the Rate Limiting form</a>, you want to try it.<br><br>Renewals, i.e., repeated certification requests, which contain exactly the same set of domains are not counted into this limit, even if the existing certificate already expired.<br><br>
						<i>Note 1: Staging/test environment has the limit of 30,000 certificates per domain per week.</i>
						<br><i>Note 2: We are not sure if there is a limitation on how long the existing certificate can be expired for a relevant request being counted as a renewal.</i>
						<br><i>Note 3: It gets a bit complicated when you request new certificates as well as renew existing one - not sure if this is definitive but here we go. Renewals (see below) count to the weekly limit, but they are not limited by it. Basically, you can always renew your existing certificates.<br>
						A side-effect of that is, however, you will have to be careful and plan well when you get to a threshold of about 240 certificates, as renewals may easily eat out the whole quota for each week. See examples below. </i><br>
					</p>

					<h3 class="mg-md  tc-rich-electric-blue">
						Preference of IPv6 (and failures of misconfigured clients)
					</h3>
<p class="mg-md">
	At the end of May 2017, Let's Encrypt changed the handling of
	IPv4 and IPv6 - IPv6 became the preferred protocol. This change has been causing sudden malfunctions of clients as
	domain validation started failing if there was a problem with IPv6 configuration.
</p>
					<p class="mg-md">
						Previously, Let's Encrypt preferred IPv4, which is still the protocol you are likely
						to configure and test your web browser with first. You may need to update network configuration on
						your servers so that IPv6 requests reach your server and the web server recognizes
						IPv6 addresses as belonging to existing (virtual) hosts.
					</p>

					<p class="mg-md">
						Some users of Let's Encrypt, however, were caught by surprise, as  IPv6 issues were
						not due to their servers' configuration, but errors in network traffic routing
						provided by their internet provider.
					</p>

					<p class="mg-md">
						You can use <a class="ltc-rich-electric-blue" href="http://ipv6-test.com/">this online service to test your server</a>.
						More details  <a class="ltc-rich-electric-blue" href="https://community.letsencrypt.org/t/preferring-ipv6-for-challenge-validation-of-dual-homed-hosts/34774">
							here (Let's Encrypt community)</a>.
					</p>

					<h3 class="mg-md  tc-rich-electric-blue">
						Max registrations per end-point (IPv4, IPv6)
					</h3>
					<p class=" mg-sm">
					Let's Encrypt limits the number of registrations (i.e., creation of account keys) per end point. The values are:
					</p>
					<ul>
						<li>
							<p>
								IPv4 - exact IP address - 10 registrations per 3 hours; and
							</p>
						</li>
						<li>
							<p>
								IPv6 - /48 range and is 500 per 3 hours.
							</p>
						</li>
					</ul>
					<p class=" mg-lg">
						<i>Note 1: these limits are the same for production as well as staging environment.</i><br><br>
						<i>Note 2: re-use of an account key - the key created during registration is not allowed. Each account needs its own key. </i>
					</p>

					<h3 class="mg-md  tc-rich-electric-blue">
						Staging environment
					</h3>
					<p class=" mg-lg">
						Do use "--dry-run" or an available alternative when you test your integration. Certificate issuance works exactly the same except for the certificate being only for testing, i.e., not trusted.<br><br>If you don't do that, you will run out of your weekly quota of certificates very quickly. It's not the end of all day, you just have to wait a week to request a new production LE certificate. Meantime, you can still test with the LE staging environment.<br><br>
						<b>When testing, use "--dry-run" or an equivalent to avoid quota-based restrictions.</b><br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Floating window for limits
					</h3>
					<p class=" mg-lg">
						LE enforces several "velocity" limits, i.e., how many requests you can submit to its certification authority. These are currently based on a floating window of 7 days, i.e., 168 hours. Your "allowance" is recomputed at the time of each new certification request using logs of the last 168 hours.<br><br><i>All limits are only enforced in the production environment. The staging environment is open for your testing. (<a class="ltc-rich-electric-blue" href="https://letsencrypt.org/docs/staging-environment/">There are any limits in the staging environment</a>, but limiting values are much higher. We haven't hit any yet.)</i><br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Does revocation of a certificate reset limit counters?
					</h3>
					<p class="mg-lg ">
						No.
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Maximum number of renewals
					</h3>
					<p class=" mg-sm">
						There is a limit on the maximum number of certificate renewals. This is currently 5 per week per certificate. A request for a certificate is counted as a renewal, if it contains exactly the same set of domain names. Mind the following two friendly rules:<br>
					</p>
					<ul>
						<li>
							<p>
								domain names are case insensitive (EnigmaBridge.com and enigmabridge.com are equal); and
							</p>
						</li>
						<li>
							<p>
								domains can be in any order.
							</p>
						</li>
					</ul>
					<p class="mg-md">
						<i>Note: the staging/test environment has a limit of 50,000 renewals per account per week. (The number is correct, it is not clear, whether it is per account.) </i>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Adding a domain name to an existing certificate
					</h3>
					<p class="mg-lg ">
						If you change the set of domain names - add at least one, or remove at least one, the subsequent request is counted as a new certificate. It will count towards the "Max certificate requests" limit above.<br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Combination of renewals and new certificate requests
					</h3>
					<p class="mg-lg">As there are two separate rules limiting the number of requests, be aware that if you reach any of them, the request will be rejected.</p>
					<p class="mg-lg ">
						<b>Example 1</b><br><br>
						Let's say that you have been doing some testing and requested 5 certificates for "www.keychest.net" in one day (you reached the domain renewal limit).<br><br>
						You know these were the only requests in the last 7 days (the limiting time window).<br><br>
						If you now decide to add this domain name to another certificate you already have on your server (e.g., containing "mx.keychest.net", and "ssh.keychest.net"). A request for a new certificate with all three domains will be accepted, as it is not a renewal but a new certificate request.<br>
					</p>
					<p class="mg-lg ">
						<b>Example 2</b><br><br>
						Let's say that you have been doing some testing and requested 5 certificates for "www.keychest.net" in one day (you reached the domain renewal limit).<br><br>
						You have also requested 15 other certificates, which in effect used up all your certificate allowance for the 7 days' time window, as renewals <b>do count</b> into that limit.<br><br>
						The same request as in Example 1 will now be rejected. The reason being you reached the weekly limit for new certificates.<br>
					</p>
					<p class="mg-lg ">
						<b>Example 3</b><br><br>
						You have used up all your certificate allowance for the 7 days' time window. Also, you have only done 4 renewals of the "www.keychest.net" certificate, so you're allowed to do 1 more renewal.<br><br>
						The same request as in Example 2 will be rejected. The reason is you reached the weekly limit for new certificates and as the list of domains is different, it is a request for a new certificate.<br>
					</p>
					<p class="mg-lg ">
						<b>Example 4</b><br><br>
						You haven't done any certification requests in the last 7 days and you need to do 10 renewals and get 15 new certificates.<br><br>
						You start with 10 renewals of your existing certificates.<br><br>
						If you now request 15 new certificates, you can only get 10 of them. The remaining 5 will have to wait for seven days.
					</p>
					<p class="mg-lg ">
						<b>Example 5</b><br><br>
						You haven't done any certification requests in the last 7 days and you need to do 10 renewals and get 15 new certificates.<br><br>
						You start with new certificates and get all 15 of them.<br><br>
						You can now proceed with all 5 renewals, as they count towards the certificate limit, but not restricted by it.
					</p>
					<p class="mg-lg ">
						<b>Example 6</b><br><br>
						You have a lot of certificates and have been randomly adding them as needed so you have to do 20 renewals over any 7 day window.<br><br>
						If you need even more certificates, you will eventually have to plan in weekly schedules. First, you will request new certificates you know you need in the next 7 days. Then you renew all that is needed as quickly as possible afterwards. You can then request new certificates 7 days later.
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Limit on failed authorization
					</h3>
					<p class="mg-lg ">
						If your automation doesn't work, or you fail to validate domain ownership (e.g., adding files in your web root folder, or amending your DNS records), there is a limit of 5 failed validations per domain per hour.<br><br>
						Please note there is also a limit on pending validations - see below in "Velocity limits 2".

						<br><br><i>Note: The staging/test environment has a limit of 60 failed validations per hour per account.</i>
					</p>
					<h3 class="mg-md tc-rich-electric-blue">
						Completing certification request
					</h3>
					<p>
						LE certificate issuance consists of 3 steps and they have to be completed within 7 days, although clients usually do them all within a few seconds when fully automated. <br> <br>The steps are: <br>1. authorization request; <br>2. providing a proof of domain ownership; and <br>3. certificate issuance. <br> <br>LE servers will create a unique random value (a secret or token), which you receive securely. You have to use it to prove that you control the domain name, for which you requested the certificate. Once you&rsquo;ve done that, you can proceed with step 3 above. <br> <br>All three steps can be done in one go and completed within seconds. However, step 2 may require manual steps, e.g., to change domain name service (DNS) records. That&rsquo;s why LE implemented two different time limits: <br>
					</p>
					<ul>
						<li>
							<p>
								7 days - to complete step 2; and
							</p>
						</li>
						<li>
							<p>
								30 days (currently) - to use a valid authorization to get a new certificate.
							</p>
						</li>
					</ul>
					<div class="divider-h">
						<span class="divider"></span>
					</div>
					<h2 class="mg-md tc-rich-electric-blue">
						Implementation limits
					</h2>
					<h3 class="mg-md  tc-rich-electric-blue">
						Velocity limits 1
					</h3>
					<p class="mg-lg ">
						There is a limit of 20 per seconds for your servers (endpoints) related to new certificates or revocation of certificates. If you use OCSP to verify certificates, there is a limit of 2,000/s per server.<br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Velocity limits 2
					</h3>
					<p class="mg-lg ">
						When you start using LE clients, they will create an LE account for your endpoint. This client account is used to authorize your requests. Usually, you will have one account per server, but you can share the account across your servers.<br><br>If you provide hosting, you may want to create an account per each user, and all that on a dedicated server (a server with one IP address).
						There is a limit of 500 new accounts per IP address. <a class="ltc-rich-electric-blue" href="https://letsencrypt.org/docs/integration-guide/">Let's Encrypt Integration Guide</a> also recommends using a single account for all customers.<br>
						<br><br>If you make a mistake somewhere, typically when changing clients, and you don't complete certificate issuance, it is possible that LE servers will keep "pending authorizations". There is a limit of 300 pending authorizations per account. If you need to clear them quicker, please follow&nbsp;<a class="ltc-rich-electric-blue" href="https://community.letsencrypt.org/t/clear-pending-authorizations/22157/2" target="_blank">instructions here if you get into this situation</a>. <br> <br>
						Sliding window of 7 days applies here as well.<br><br><i>These limits are again applied only in the Production environment!</i><br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Validity of authorizations
					</h3>
					<p class=" mg-sm">
						Here it gets very technical, but also interesting. There is a separate validity time limit for authorizations, which is currently 30 days (as of 30 June 2017). What it means is that if you request a certificate renewal, your client will not have to request new authorization for 30 days. It means that you need to:<br>
					</p>
					<ul>
						<li>
							<p>
								spin a temporary web server - you have to stop any other web server, which is using port 443 (check your LE client and the web server if this can be done without stopping the web server; e.g., certbot with "--apache" or "--nginx" may work) or
							</p>
						</li>
						<li>
							<p>
								allow creation of temporary verification files in your web root folder - you need to have access to write into the directory with your web data; or
							</p>
						</li>
						<li>
							<p>
								update DNS records - again, you need to amend your DNS records.
							</p>
						</li>
					</ul>
					<p class="mg-lg ">
						Only once every 30 days. This authorization is per account and per domain name. If you have "ssh.enigmabrige.com" and "mx.enigmabridge.com" pointing to the same server, but each name has its own certificate, you will need 2 authorizations.<br><br>For an authorization to live for 30 days, you have to successfully verify it within 7 days. Otherwise, it may stay in the "pending" state and will be removed after 7 days. This limit is particular important if you use DNS verification, as this is a limit in which you need to amend your DNS records.<br>
					</p>
					<div class="divider-h">
						<span class="divider"></span>
					</div>
					<h2 class="mg-md tc-rich-electric-blue">
						Monitoring
					</h2>
					<h3 class="mg-md  tc-rich-electric-blue">
						Email reminders
					</h3>
					<p class=" mg-sm">
						Letsencrypt will send you reminders to renew your certificates. These are sent:<br>
					</p>
					<ul>
						<li>
							<p>
								20 days before the date of expiry;
							</p>
						</li>
						<li>
							<p>
								10 days before the date of expiry; and<br>
							</p>
						</li>
						<li>
							<p>
								1 day before the expiry.
							</p>
						</li>
					</ul>
					<p class=" mg-lg">
						<i>Note: when you successfully request a certificate with a changed list of domain names, you will keep receiving reminders as you got a new certificate, instead of a renewal of as the original one.</i><br><br>
						The actual time varies and can be a day or even two days later.<br><br>These email reminders are sent to the email address you enter when you create your account key - this is usually the first certification request on a given server. The email is stored with your letsencrypt account configuration. You can also set an arbitrary email address with each request, if your Let's Encrypt client supports it.<br><br>Bear in mind that if you unsubscribe from these notifications, you can't re-subscribe. It may also happen that these emails end up in your spam / junk mail box.<br><br>Try our&nbsp;<a class="ltc-rich-electric-blue" href="https://keychest.net">free KeyChest service</a>&nbsp;to get weekly emails with all expiration dates in one place.<br>
					</p>
					<h3 class="mg-md tc-rich-electric-blue">
						Planning
					</h3>
					<p>
						KeyChest is our attempt to give you an enterprise certificate management system for your Let&rsquo;s Encrypt certifcates. It is a free service for absolute majority of you, including weekly email summaries. <br> <br>If you want to manage other types of certificates you issue internally, or your internal networks as such, you will need either internal agent, or an instance of KeyChest installed on one of your own servers. This configuration requires a license.
					</p>
				</div>
			</div>
		</div>
	</div>
	<!-- bloc-4 END -->

@endsection

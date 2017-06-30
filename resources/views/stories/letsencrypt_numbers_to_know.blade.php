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
						<a class="ltc-rich-electric-blue" href="https://letsencrypt.org" target="_blank">Let's Encrypt</a>&nbsp;is now the largest certificate provider for publicly facing internet servers. It does not issue the most secure certificates (i.e., EV, or extended validation certificates), but its certificates provide a very good level of security for most of us.<br><br>When we started using Let's Encrypt (LE), we slowly learnt about various limitations imposed on users. There is not any single place where you can find all important information in one place so here's the first attempt. We will amend it as we learn more directly, or from your feedback.<br>
					</p>
					<div class="divider-h">
						<span class="divider"></span>
					</div>
					<h2 class="mg-md  tc-rich-electric-blue">
						Business restrictions
					</h2>
					<h3 class="mg-md  tc-rich-electric-blue">
						Restrictions on certificate use
					</h3>
					<p class=" mg-lg">
						Use of certificates is limited by their <strong>profile</strong>. LE offers just one certificate profile and there is no flexibility in this. The main goal is to automate certificate issuance and this is an acceptable restriction. Certificates limit the use to digital signatures and "key encipherment". From our point of view, they can be used only for:<br>1. server authentication; and<br>2. client authentication.<br><br>The good news is, you can use it for any of your internet servers - web, email, SSH, web apps, and so on.<br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Time validity
					</h3>
					<p class=" mg-lg">
						This is a property people talk about a lot. Certificates are valid for 90 days exactly. Exact means that if you get your certificate at 8:31am, it will expire 90 days later at 8:31am.<br><br>LE says the main reason is to encourage automation on our side. I'm not sure it quite works yet, but we are trying to chip in with some services here.<br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Multi-domain aka SAN Certificates
					</h3>
					<p class=" mg-sm">
						You can request a certificate, which will contain up to 100 domain names. These are basically server names, unless you are happy to:<br>
					</p>
					<ul>
						<li>
							<p>
								copy your private key across servers; and
							</p>
						</li>
						<li>
							<p>
								implement DNS load-balancing / load-distribution.
							</p>
						</li>
					</ul>
					<p class=" mg-lg">
						The limit here is 100 domain names per certificate. Technically, it is implemented as one main “subject" name and 99 alternative names.
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						No wildcard certificates<br>
					</h3>
					<p class=" mg-lg">
						Wildcard and DV (domain validation) certificates are not available. This relates to similar restrictions on EV (extended validation) certificates.<br>
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
								RSA keys, with lengths from 2048b to 4096b;
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
						If you start building a bigger network infrastructure and assign servers a public name within your company domain (e.g., enigmabridge.com), you should be aware that you can't get more than 20 certificates per week per registered domain.<br><br>If you think about a cloud service with subdomains per customer, this may be a serious problem. LE offers an opportunity to request an increase of this limit but they don't guarantee positive response or any response at all. Here's&nbsp;<a class="ltc-rich-electric-blue" href="https://docs.google.com/forms/d/e/1FAIpQLSfg56b_wLmUN7n-WWhbwReE11YoHXs_fpJyZcEjDaR69Q-kJQ/viewform?c=0&amp;w=1" target="_blank">the address of the Rate Limiting form</a>, you want to try it.<br><br>Renewals, i.e., repeated certification requests, which contain exactly the same set of domains are not counted into this limit, even if the existing certificate already expired.<br><br><i>Note: We are not sure if there is a limitation on how long the existing certificate has been expired.</i><br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Staging environment
					</h3>
					<p class=" mg-lg">
						Do use "--dry-run" or an available alternative when you test your integration. Certificate issuance works exactly the same except for the certificate being only for testing, i.e., not trusted.<br><br>If you don't do that, you will run out of your weekly quota of certificates very quickly. It's not the end of all day, you just have to wait a week to request a new production LE certificate. Meantime, you can still test with the LE staging environment.<br><br>*When testing, use "--dry-run" or an equivalent to avoid quota-based restrictions.*<br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Floating window for limits
					</h3>
					<p class=" mg-lg">
						LE enforces several "velocity" limits, i.e., how many requests you can submit to its certification authority. These are currently based on a floating window of 7 days, i.e., 144 hours. Your "allowance" is recomputed at the time of each new certification request using logs of the last 144 hours.<br><br><i>All limits are only enforced in the production environment. The staging environment is open for your testing. (I don't know if there are any limits in the staging environment, but we haven't hit any yet.)</i><br>
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
						There is a limit on the maximum number of certificate renewals. This is currently 5 per week. A request for a certificate is counted as a renewal, if it contains exactly the same set of domain names. Mind the following two friendly rules:<br>
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
					<h3 class="mg-md  tc-rich-electric-blue">
						Adding a domain name to an existing certificate
					</h3>
					<p class="mg-lg ">
						If you change the set of domain names - add at least one, or remove at least one, the subsequent request is counted as a new certificate. It will count towards the "Max certificate requests" limit above.<br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Combination of renewals and new certificate requests
					</h3>
					<p class="mg-lg ">
						As there are two separate rules limiting the number of requests, be aware that if you reach any of them, the request will be rejected.<br><br>Let's say that you have been doing some testing and requested 5 certificates for "www.keychest.net" in one day (you reached the domain renewal limit) and you know these were the only request in the last 7 days. A few hours later you realize that it would be much better to add this domain name to another certificate you already have on your server (e.g., containing "mx.keychest.net", and "ssh.keychet.net"). If you now request a new certificate with all three domains (in my example here), the request will be rejected. The reason is the limit per domain renewals for "www.keychest.net".<br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Limit on failed authorization
					</h3>
					<p class="mg-lg ">
						If your automation doesn't work, or you fail to validate domain ownership (e.g., adding files in your web root folder, or amending your DNS records), there is a limit of 5 failed validations per domain per hour.<br>
					</p>
					<h3 class="mg-md tc-rich-electric-blue">
						Completing certification request
					</h3>
					<p>
						LE certificate issuance consists of 3 steps and they have to be completed within 7 days. <br> <br>The steps are: <br>1. authorization request; <br>2. providing a proof of domain ownership; and <br>3. certificate issuance. <br> <br>LE servers will create a unique secret, which you receive securely. You have to use it to prove that you control the domain name, for which you requested the certificate. Once you&rsquo;ve done that, you can proceed with step 3 above. <br> <br>All three steps can be done in one go and completed within seconds. However, step 2 may require manual steps, e.g., to change domain name service (DNS) records. That&rsquo;s why LE implemented two different time limits: <br>
					</p>
					<ul>
						<li>
							<p>
								7 days - to complete step 2; and
							</p>
						</li>
						<li>
							<p>
								60 days (currently) - to use a valid authorization to get a new certificate.
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
						When you start using LE clients, they will create an LE account for your endpoint. This client account is used to authorize your requests. Usually, you will have one account per server, but you can share the account across your servers.<br><br>If you provide hosting, you may need to create an account per each user, and all that on a dedicated server (a server with one IP address). There is a limit of 500 new accounts per IP address.<br><br>If you make a mistake somewhere, typically when changing clients, and you don't complete certificate issuance, it is possible that LE servers will keep "pending authorizations". There is a limit of 300 pending authorizations per account. If you need to clear them quicker, please follow&nbsp;<a class="ltc-rich-electric-blue" href="https://community.letsencrypt.org/t/clear-pending-authorizations/22157/2" target="_blank">instructions here if you get into this situation</a>. <br> <br>Sliding window of 7 days applies here as well.<br><br><i>These limits are again applied only in the Production environment!</i><br>
					</p>
					<h3 class="mg-md  tc-rich-electric-blue">
						Validity of authorizations
					</h3>
					<p class=" mg-sm">
						Here it gets very technical, but also interesting. There is a separate validity time limit for authorizations, which is currently 60 days. What it means is that if you request a certificate renewal, your client will not have to request new authorization for 60 days. It means that you need to:<br>
					</p>
					<ul>
						<li>
							<p>
								spin a temporary web server - you have to stop any other web server, which is using port 443; or
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
						Only once every 60 days. This authorization is per account and per domain name. If you have "ssh.enigmabrige.com" and "mx.enigmabridge.com" pointing to the same server, but each name has its own certificate, you will need 2 authorizations.<br><br>For an authorization to live for 60 days, you have to successfully verify it within 7 days. Otherwise, it may stay in the "pending" state and will be removed after 7 days. This limit is particular important if you use DNS verification, as this is a limit in which you need to amend your DNS records.<br>
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

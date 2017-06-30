@extends('layouts.stories')

@section('content-nav')
@endsection

@section('story')

	<!-- bloc-3 -->
	<div class="bloc bgc-white l-bloc tc-onyx" id="bloc-3">
		<div class="container bloc-sm">
			<div class="row">
				<div class="col-sm-12">
					<h1 class="mg-md  tc-rich-electric-blue">
						KeyChest spot checks
					</h1>
					<h2 class="mg-md  tc-rich-electric-blue">
						What is KeyChest and its spot check good for?
					</h2>
					<p class=" mg-lg">
						You may ask why you need another tool, when there's <a class="ltc-rich-electric-blue" href="https://www.ssllabs.com/ssltest">SSL Labs</a>, which tells me all I need about how secure my website is. The answer is simple,&nbsp;<a class="ltc-rich-electric-blue" href="https://keychest.net">KeyChest</a><br>is much more about keeping your servers available, than secure.<br><br>The answer is simple - to keep your business up and running. It may be prudent to reach A+ rating at SSL Labs in April, but it doesn't help if your customers can't access your online store in July.<br><br><a class="ltc-rich-electric-blue" href="https://keychest.net">KeyChest</a>&nbsp;gives you all the information you need to keep your servers' SSL certificates up to date, and preventing downtimes caused by improperly configured or expired certificates. We also help you set up your serves so that your users, customers, and clients can use them and connect to them.<br><br><a class="ltc-rich-electric-blue" href="https://keychest.net">KeyChest</a>&nbsp;will tell you when any of your servers needs a new certificate, if your server sends all the certificates properly, and what was your downtime over last 2 years. You get all that information for all of your servers in one place, with a fresh update once a week into your mailbox.<br><br>We also want to show you who issued your certificate and how to save money on&nbsp;the next one. Make you aware of any new certificates issued for any of your servers. One of the main extensions would be integration of reminders into your calendar.<br><br>But back to the bottomline. If I am pushed to name a single issue that I believe is key for secure website it will definitely be <strong>time validity</strong>.<br><br>The biggest problem of security your servers is that you need to create a new key and certificate. It can be every three months, or once in 2-3 years. The harsh reality is that if you don't do it, your online business will stop working.<br>
					</p>
					<h2 class="mg-md  tc-rich-electric-blue">
						SSL spot checks - setting servers up
					</h2>
					<p>
						<a class="ltc-rich-electric-blue" href="https://keychest.net">KeyChest spot checks</a>&nbsp;help you quickly check a particular server. The check takes just a few seconds so it is really useful for troubleshooting basic configuration.<br><br>You can repeat the spot check as many times as needed until everything works. Once you're happy with the configuration, we would suggest the following steps steps:<br>
					</p>
					<ul>
						<li>
							<p>
								You can send the URL of your last spot check to your colleagues or boss - we store results of each spot check, so they will see exactly what you did.
							</p>
						</li>
						<li>
							<p>
								Start tracking the server if you haven't yet.
							</p>
						</li>
						<li>
							<p>
								Run the server past the SSL Labs checker for a through review of cryptographic configuration.
							</p>
						</li>
					</ul>
					<p class=" mg-lg">
						<i>Note: KeyChest will run any checks against a particular server, while SSL Labs follows redirects and will ultimately provide results for the "domain name", rather than server.</i>
					</p>
					<h2 class="mg-md  tc-rich-electric-blue">
						The scope of spot checks
					</h2>
					<p>
						Let me quickly introduce a list of checks we execute against your selected server:<br>
					</p>
					<ul>
						<li>
							<p>
								<strong>time validity</strong> — we check how long till the certificate expires. There are three possible warning messages you can see: PLAN (if the expiration date is in less than 28 days), WARNING (if the expiration is in less than 2 days), or ERROR (the server is not available).
							</p>
						</li>
						<li>
							<p>
								<strong>chain trust</strong> — each server should send all certificates to simplify verification for clients. Most web browsers will still be able to verify security of your server most of the time, if there&rsquo;s a certificate missing. It may however cause a problem and we will show you if it is the case.
							</p>
						</li>
						<li>
							<p>
								<strong>hostname</strong> — each certificate shows on which servers it can be used. Mismatch here means that web browser will detect a security problem.
							</p>
						</li>
						<li>
							<p>
								<strong>TLS version</strong> — you&rsquo;re strongly encouraged to use TLS version 1.2 and the checker will tell you if it&rsquo;s anything worse than that.
							</p>
						</li>
						<li>
							<p>
								<strong>multi-domain certs and wildcards</strong> (aka neighbors) — some certificates are shared among several servers and it's good to see that this list is correct. It also shows, how many servers share the same key.
							</p>
						</li>
						<li>
							<p>
								<strong>downtime in 2 years</strong> — this number is the minimum estimate and actual downtime could be much longer. This check uses purely data from&nbsp;<a class="ltc-rich-electric-blue" href="https://www.certificate-transparency.org/" target="_blank">certificate transparency (CT)</a>&nbsp;logs. Sometimes certificate exist, but they are not deployed to web servers - that's why the actual downtime might have been longer.
							</p>
						</li>
						<li>
							<p>
								<strong>strict transport security (HSTS) </strong>— Your webserver can request web browsers to require SSL connections all the time. This prevents some attacks and we will mention it if the checker detects this configuration.
							</p>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<!-- bloc-3 END -->

@endsection

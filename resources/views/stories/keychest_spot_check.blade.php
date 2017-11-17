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
						You may ask why you need another tool, when there's
						<a class="ltc-rich-electric-blue" href="https://www.ssllabs.com/ssltest">SSL Labs</a> auditing
						tool, which tells you all you need about the security of your website. The answer is simple,&nbsp;
						<a class="ltc-rich-electric-blue" href="https://keychest.net">KeyChest</a><br>is much more
						about keeping your servers available, than secure. You can only do a thorough security audit
						on servers, which have been configured and use the certificate you want.<br><br>

						KeyChest.net is about keeping your business up and running. It may be prudent to reach A+ rating
						at SSL Labs in April, but it doesn't help if your customers can't access your online store
						in July.<br><br>

						<a class="ltc-rich-electric-blue" href="https://keychest.net">KeyChest</a>&nbsp;gives
						you all the information you need to keep your servers' SSL certificates up to date. It allows you
						to plan certificate renewals and tells you when something broke and needs a closer look. This
						protects you from downtimes as you can plan certificate renewals with enough to resolve any
						potential problems. Spot checks of KeyChest also
						help you set up your servers so that your users, customers, and clients can use them and connect
						to them reliably as we detect issues that may cause random unexpected problems to access your
						web services.<br><br>

						<a class="ltc-rich-electric-blue" href="https://keychest.net">KeyChest</a>&nbsp;features a powerful
						Dashboard with details of all your certificates in one place. Dashboard tables list relevant issues,
						from DNS lookup errors, incomplete trust chains, or certificate expiration dates. <br><br>

						If you just want to keep an eye on your administrators, KeyChest will send you a brief email
						with all the important metrics.<br><br>

						We also want to show you who issued your certificate and how to save money on&nbsp;the next one.
						Make you aware of any new certificates issued for any of your servers. One of the main extensions
						would be integration of reminders into your calendar.<br><br>


						The biggest problem of the security of your servers is that you need to create a new key and certificate.
						It can be every three months, or once in 2-3 years. The harsh reality is that if you don't do it,
						your online business will simply grind to halt.<br>
					</p>
					<h2 class="mg-md  tc-rich-electric-blue">
						SSL spot checks - setting up new servers
					</h2>
					<p>
						<a class="ltc-rich-electric-blue" href="https://keychest.net">KeyChest spot checks</a>&nbsp;help you
						quickly check a particular server. The check takes just a few seconds so it is really useful for
						troubleshooting basic configuration.<br><br>You can repeat the spot check as many times as
						needed until everything works. Once you're happy with the configuration, you can let KeyChest
						keep an eye on the server for you, just start watching it. But you can also:<br>
					</p>
					<ul>
						<li>
							<p>
								Send the URL of your last spot check to your colleagues or boss - we store results of
								each spot check, so they will see exactly what you did.
							</p>
						</li>
						<li>
							<p>
								Run the server past the SSL Labs checker for a thorough review of its cryptographic
								configuration.
							</p>
						</li>
					</ul>
					<p class=" mg-lg">
						<i>Note: KeyChest will run any checks against a particular server, while SSL Labs test follows
							redirects and will ultimately provide results for the "domain name", rather than the server.
						KeyChest allows you check even a particular IP address so it's easy to get results of new servers
						even in HA, DNS round robin, or other resilient configurations.</i>
					</p>
					<h2 class="mg-md  tc-rich-electric-blue">
						The scope of spot checks
					</h2>
					<p>
						Here is a list of checks we execute against your selected server:<br>
					</p>
					<ul>
						<li>
							<p>
								<strong>time validity</strong> — we check how long till the certificate expires. There
								are three possible warning messages you can see: PLAN (if the expiration date is in less
								than 28 days), WARNING (if the expiration is in less than 7 days), or ERROR (the server
								is not available).
							</p>
						</li>
						<li>
							<p>
								<strong>trust chain</strong> — each server should send all certificates to simplify
								verification for clients. Most web browsers will still be able to verify security of
								your server most of the time, if there&rsquo;s a certificate missing. It may however cause a
								problem and we will show you if it is the case.
							</p>
						</li>
						<li>
							<p>
								<strong>hostname</strong> — each certificate shows a list of servers, on which  it can
								be used. Mismatch here means that the web browser will detect a security problem.
							</p>
						</li>
						<li>
							<p>
								<strong>TLS version</strong> — we strongly encourage you  to use TLS version 1.2 and
								the checker will tell you if it&rsquo;s anything worse than that.
							</p>
						</li>
						<li>
							<p>
								<strong>multi-domain certs and wildcards</strong> (aka neighbors) — some certificates
								are shared among several servers and it's good to see that this list is correct. It
								also shows, how many servers share the same key.
							</p>
						</li>
						<li>
							<p>
								<strong>downtime in 2 years</strong> — this number is the minimum estimate and actual
								downtime could be much longer. This check uses purely data from&nbsp;
								<a class="ltc-rich-electric-blue" href="https://www.certificate-transparency.org/"
								   target="_blank">certificate transparency (CT)</a>&nbsp;logs. Sometimes certificates exist,
								but they are not properly installed in web servers - that's why the actual downtime might have
								been longer.
							</p>
						</li>
						<li>
							<p>
								<strong>strict transport security (HSTS) </strong> — Your web server can request web
								browsers to require SSL connections all the time. This prevents some attacks and we
								will mention it if the checker detects this configuration.
							</p>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<!-- bloc-3 END -->

@endsection

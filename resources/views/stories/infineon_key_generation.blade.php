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
						ROCA - vulnerability in Infineon RSA key generation
					</h1>

					<p class="mg-lg">
						Quantum cryptography is still some years away from being anything but an interesting
						research area. But if you want to see what it is to suddenly have all your keys broken,
						look the ROCA vulnerability.
					</p>

					<p>
						The ROCA vulnerability enables computation of RSA private keys from their public components for a range of key lengths, including commonly used 2,048 bits with low to medium budget. A successful computation of a private key allows, depending on its use, decrypt sensitive data (from file encryption to HTTPS), forging digital signatures (email security, qualified signatures), impersonation (access control to IT systems or buildings), or personal identity theft (e-ID cards).

						The current (conservative) indicative processor times for 1,024 and 2,048 bit keys are as follows.
					</p>
						<ul>
						<li><p>1,024 bit RSA keys – 97 vCPU days (maximum cost of $40-$80); and</p></li>
						<li><p>2,048 bit RSA keys – 51,400 vCPU days, (maximum cost of $20,000 - $40,000).</p></li>
					</ul>
					<p>
					Computations can be split among an arbitrary number of processors and completed in days, hours, or even minutes. These time and cost estimates were valid at the time of publication and may decrease.
					</p>


					<p>
					You can now read all the technical details in <a href="https://dl.acm.org/citation.cfm?id=3133969" target="_blank" >the ACM Digital Library, which published the full text of the research paper </a>.
					</p>

					<p>
					While the paper describes the technical details, if you want to understand
						real-world implications than it takes a bit more imagination. Let's start with this example
						- your company decided to outsource your IT to third parties. You need to show your clients
						and partners that you're in control of your data and use encryption to protect against
						unauthorized access via the IT supplier. As you are worried about ransomware, you create
						frequent backups - actually your IT supplier does.
					</p>

					<p>
					October 16th came and you suddenly learnt that it was possible to decrypt all your
						sensitive data without any additional information - all that was needed had been
						included with the data, namely public keys used for encryption. And it's not just
						an odd misplaced key that got compromised - it's all your keys at the same time.
					</p>

					<p>
					You can revoke the keys, but the data is out there and there's no way to hide it now.
						All you can do is to hope that there are so many other companies, that your IT
						supplier will be helpful, that you know of all the copies of your data, that no one
						will find your data worth $20,000 (or maybe just $500 dollars when November comes
						and black hats optimize the attack) to crack the key.
					</p>


					<h2 class="mg-md  tc-rich-electric-blue">Assessment and mitigation</h2>

					<p>
					The vulnerability can’t be used for large-scale attacks but there is a practical method for targeted attacks.
						Do contact manufacturers of products you suspect may be affected, or check their latest bug and press releases, and product updates for more information.
					</p>

					<p>
						If you suspect you or your organization's security may be at risk, we have implemented a tool
						to test RSA public keys for the ROCA vulnerability. It is available for download and as an online
						test toolkit here at:
					</p>

					<ul>
						<li><p><a class="ltc-rich-electric-blue" href="https://keychest.net/roca">https://keychest.net/roca</a></p></li>
					</ul>

					<p>
						A list of possible mitigation steps includes:
					</p>

					<ul>
						<li><p>install security updates provided by manufacturers if available;</p></li>
						<li><p>replacement of security chips / products with these chips with secure ones;</p></li>
						<li><p>change the source of RSA keys to a secure key generator;</p></li>
						<li><p>replace RSA algorithm with an elliptic curve encryption (ECC);</p></li>
						<li><p>shorten the lifetime of RSA keys;</p></li>
						<li><p>limit access to repositories with public keys; or</p></li>
						<li><p>separation of data-at-rest and data-in-transit encryption.</p></li>
					</ul>


				</div>
			</div>
		</div>
	</div>
	<!-- bloc-3 END -->

@endsection

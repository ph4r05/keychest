@extends('layouts.stories')

@section('content-nav')
@endsection

@section('story')

	<!-- bloc-5 -->
	<div class="bloc bgc-white tc-onyx" id="bloc-5">
		<div class="container bloc-sm">
			<div class="row">
				<div class="col-sm-12">
					<h1 class="mg-md  tc-rich-electric-blue">
						Understand spot checks
					</h1>
					<p class=" mg-lg">
						We have implemented spot checks so that they return as little information as necessary. The main point was simplicity. If there is a problem that needs to be fixed, you will see it. If you get everything right, you will get thumbs up. <br> <br>When you start using it, however, you may wonder why you are shown certain information. This page explains what we mean, when we show you certain information to re-assure you about the results of your tests.
					</p>
					<h2 class="mg-md  tc-rich-electric-blue">
						Which server you test
					</h2>
					<p>
						KeyChest checks servers we resolve using the domain name you give us. If the domain name leads to, e.g., a web-server, which then redirects your browser, we will ignore this redirection. <br> <br>The initial scanning will be done against the “primary” server, if we detect a re-direction, you will see a text allowing you to do a one-click scan of another server, the target of the redirection.
					</p>
					<h2 class="mg-md  tc-rich-electric-blue">
						Time validity messages
					</h2>
					<p>
						We show four different messages according to how much time is left till the certificate expires.<br>
					</p>
					<ul class="list-unstyled">
						<li>
							<p>
								OK - there is more than 28 days left till the certificate expiration date;
							</p>
						</li>
						<li>
							<p>
								PLAN - if the time left is between 2 and 28 days;
							</p>
						</li>
						<li>
							<p>
								WARNING - if there&rsquo;s less than 48 hours left before your server becomes unavailable;
							</p>
						</li>
						<li>
							<p>
								ERROR - your server is not available to your customers and clients as the certificate has expired.
							</p>
						</li>
					</ul>
					<h2 class="mg-md tc-rich-electric-blue">
						…. more content coming within days as we finalize KeyCest reporting
					</h2>
				</div>
			</div>
		</div>
	</div>
	<!-- bloc-5 END -->
@endsection

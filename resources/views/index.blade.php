@extends('layouts.app')

@section('content-nav')
    {{--<li><a onclick="scrollToTarget('#learn')">Learn more</a></li>--}}
@endsection

@section('content')
    <!-- loading placeholder -->
    <div class="bloc bloc-fill-screen tc-onyx bgc-white l-bloc" id="intro-placeholder" style="height: 200px;">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <img src="/images/logo2-rgb_keychest.png" class="img-responsive center-block" width="300">
                    <h3 class="text-center mg-lg hero-bloc-text-sub  tc-rich-electric-blue">
                        Track and plan for 100% HTTPS uptime
                    </h3>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-8 col-sm-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-body alert-waiting">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vue search component -->
    <quicksearch></quicksearch>

    <!-- learn -->
    <div class="bloc tc-onyx bgc-isabelline l-bloc" id="learn">
	<div class="container bloc-sm">
		<div class="row">
			<div class="col-sm-12">
				<h1 class="mg-md tc-rich-electric-blue">
					Welcome to KeyChest
				</h1>
				<p>
					KeyChest is the tool you need to stay on top of all your certificates and to keep your boss happy. You can use KeyChest to plan your renewals, get your weekly inventory summary and present your cert KPIs (key performance indicators) to your boss or your team. <br> <br>We don&rsquo;t mind if you use them for your web servers, email servers, on-premise web services, or to protect your infrastructure.&nbsp;We treat all certs equal, whether you paid $500 for each, got them free from LetsEncrypt, or created them yourself.<br> <br>When you create an account, you can quickly populate your dashboard using domain names with wildcards and print the first set of KPIs within minutes. <br> <br>The following table compares features of KeyChest with Letsmonitor.org.
				</p>
				<div class="row bgc-capri">
					<div class="col-sm-3">
						<h4 class="mg-md tc-onyx">
							<strong>Feature</strong>
						</h4>
					</div>
					<div class="col-sm-4">
						<h4 class="mg-md tc-onyx">
							<strong>letsmonitor.org</strong>
						</h4>
					</div>
					<div class="col-sm-5">
						<h4 class="mg-md tc-onyx">
							<strong>keychest.net</strong>
						</h4>
					</div>
				</div>
				<div class="row bgc-gainsboro">
					<div class="col-sm-3">
						<h4 class="mg-clear tc-onyx">
							<strong>Primary focus</strong>
						</h4>
					</div>
					<div class="col-sm-4">
						<p>
							networking
						</p>
					</div>
					<div class="col-sm-5">
						<h5 class=" mg-clear tc-rich-electric-blue">
							<strong>security</strong>
						</h5>
					</div>
				</div>
				<div class="row bgc-white">
					<div class="col-sm-3">
						<h4 class="mg-clear tc-onyx">
							<strong>Views</strong>
						</h4>
					</div>
					<div class="col-sm-4">
						<p>
							rule-per-server views
						</p>
					</div>
					<div class="col-sm-5">
						<h5 class=" mg-clear tc-rich-electric-blue">
							<strong>all-in-one view</strong>
						</h5>
					</div>
				</div>
				<div class="row bgc-gainsboro">
					<div class="col-sm-3">
						<h4 class="mg-clear tc-onyx">
							<strong>Adding new items</strong>
						</h4>
					</div>
					<div class="col-sm-4">
						<p>
							one server at a time
						</p>
					</div>
					<div class="col-sm-5">
						<h5 class=" mg-clear tc-rich-electric-blue">
							<strong>domains (with wildcards)</strong>
						</h5>
					</div>
				</div>
				<div class="row bgc-white">
					<div class="col-sm-3">
						<h4 class="mg-clear tc-onyx">
							<strong>Tests</strong>
						</h4>
					</div>
					<div class="col-sm-4">
						<p>
							servers directly
						</p>
					</div>
					<div class="col-sm-5">
						<h5 class=" mg-clear tc-rich-electric-blue">
							<strong>CT logs (certificate transparency), and servers (optional)</strong>
						</h5>
					</div>
				</div>
				<div class="row bgc-gainsboro">
					<div class="col-sm-3">
						<h4 class="mg-clear tc-onyx">
							<strong>Frequency</strong>
						</h4>
					</div>
					<div class="col-sm-4">
						<p>
							hourly
						</p>
					</div>
					<div class="col-sm-5">
						<h5 class=" mg-clear tc-rich-electric-blue">
							<strong>weekly and on demand</strong><br>
						</h5>
					</div>
				</div>
				<div class="row bgc-white">
					<div class="col-sm-3">
						<h4 class="mg-clear tc-onyx">
							<strong>Emails</strong>
						</h4>
					</div>
					<div class="col-sm-4">
						<p>
							certs - once before expiration
						</p>
					</div>
					<div class="col-sm-5">
						<h5 class=" mg-clear tc-rich-electric-blue">
							<strong>weekly - inventory and planner for all certs</strong>
						</h5>
					</div>
				</div>
				<div class="row bgc-gainsboro">
					<div class="col-sm-3">
						<h4 class="mg-clear tc-onyx">
							<strong>Monitoring</strong>
						</h4>
					</div>
					<div class="col-sm-4">
						<p>
							150+ stations
						</p>
					</div>
					<div class="col-sm-5">
						<h5 class="mg-clear  tc-rich-electric-blue">
							<strong>centrally, 1+ instances, additional instances for availability</strong>
						</h5>
					</div>
				</div>
				<div class="row bgc-white">
					<div class="col-sm-3">
						<h4 class="mg-clear tc-onyx">
							<strong>Security tests</strong>
						</h4>
					</div>
					<div class="col-sm-4">
						<p>
							simple
						</p>
						<ul>
							<li>
								<p>
									certs expiry on selected servers
								</p>
							</li>
						</ul>
					</div>
					<div class="col-sm-5">
						<h5 class="mg-clear  tc-rich-electric-blue">
							<strong>deployed, CT logs, cross-checking</strong>
						</h5>
						<ul>
							<li>
								<h5 class="mg-clear  tc-rich-electric-blue">
									<strong>expiry of deployed certs</strong>
								</h5>
							</li>
							<li>
								<h5 class="mg-clear  tc-rich-electric-blue">
									<strong>all issued certs</strong>
								</h5>
							</li>
							<li>
								<h5 class="mg-clear  tc-rich-electric-blue">
									<strong>difference between issued and effective certs</strong>
								</h5>
							</li>
						</ul>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<div class="row">
							<div class="col-sm-12">
								<div class="divider-h">
									<span class="divider"></span>
								</div>
								<h3 class="mg-md tc-rich-electric-blue">
									Will we ever charge you for this service?
								</h3>
								<p>
									Our plan is to keep this service free, including evolutionary features. We have some thoughts about subscriptions, but these will be only for substantial extensions of KeyChest, and customization of this service for on-premise monitoring of your internal infrastructure. Get in touch if you want to chat.&nbsp;
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
    <!-- learn END -->

    <!-- bloc-3 -->
    <div class="bloc tc-onyx bgc-isabelline " id="bloc-3">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-sm-12">
                            <feedback_form></feedback_form>
                        </div>
                    </div>
                    <p>
                        Feel free to email us at <a href="mailto:keychest@enigmabridge.com">keychest@enigmabridge.com</a>, if you have in mind particular details of a feature you’d like to see.
                    </p>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <p>
                        We are Enigma Bridge, 20 Bridge St, Cambridge, CB2 1UF, United Kingdom and we read keychest@enigmabridge.com
                    </p>
                </div>
            </div>
        </div>
    </div>
    <!-- bloc-3 END -->

    <!-- ScrollToTop Button -->
    <a class="bloc-button btn btn-d scrollToTop" onclick="scrollToTarget('1')"><span class="fa fa-chevron-up"></span></a>
    <!-- ScrollToTop Button END-->


    <!-- Footer - bloc-7 -->
    <div class="bloc bgc-white tc-outer-space" id="bloc-7">

    </div>
    <!-- Footer - bloc-7 END -->

@endsection


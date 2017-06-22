---
layout: post
title:  "LetsEncrypt in numbers - all restrictions you should know of"
introduction: Let's Encrypt is a free certificate issuance service. They do things right but they set restrictions, deadlines, an time limits you should know about.
categories: bits letsencrypt
---

[Let's Encrypt](https://letsencrypt.org) is now the largest certificate provider for publicly facing interent servers. It does not issue the most secure certificates (i.e., EV, or extended validation certificates), but its certificates provide a very good level of security for most of us.

When we started using Let's Encrypt (LE), we slowly learnt about various limitations imposed on users. There is not any single place where you can find all important information in one place so here's the first attempt. We will amend it as we learn more directly, or from your feedback. 


## Use limitations

Use of certificates is limited by their *profile*. LE offers just one certificate profile and there is no flexibility in this. The main goal is to automate certificate issuance and this is an acceptable restriction. Certificates limit the use to digital signatures and "key encipherment". From our point of view, they can be used only for:

1. server authentication; and
2. client authentication.

Good news is, you can use it for any of your internet servers - web, email, SSH, web apps, and so on.

## Time validity

This is a property people talk about a lot. Certificates are valid for 90 days exact. Exact means that if you get your certificate at 8:31am, it will expire 90 days later at 8:31am.

LE says the main reason is to encourage automation on our side. I'm not sure it quite works yet, but we are trying to chip in with some services here.

## Staging environment

Do use "--dry-run" or an available alternative when you test your integration. Certificate issuance works exactly the same except for the certificate being only for testing, i.e., not trusted.

If you don't do that, you will run out of your weekly quota of certificates very quickly. It's not the end of all day, you just have to wait a week to request a trusted certificates. Meantime, you can still test with the LE staging environment.

*When testing, use "--dry-run" or an equivalent to avoid quota-based restrictions.*


## Floating window for limits

LE enforces several "velocity" limits, i.e., how many requests you can submit to its certification authority. These are currently based on a floating window of 7 days, i.e., 144 hours. Your "allowance" is recomputed at the time of each new certification request using logs of the last 144 hours. 

*All limits are only enforced in the production environment. The staging environment is open for your testing. (I don't know if there are any limits in the staging environment, but we haven't hit any yet.)*

## Max certificate requests

If you start building a bigger network infrastructure and assign servers a public name within your company domain (e.g., enigmabridge.com), you should be aware that you can't get more than 20 certificates per week per registered domain.

If you think about a cloud service with subdomains per customer, this may be a serious problem. LE offers an opportunity to request an increase of this limit but they don't guarantee positive response or any response at all. Here's the form if you want to try it [XXXXXX]

Renewals, i.e., repeated certification requests, which contain exactly the same set of domains are not counted into this limit, even if the existing certificate already expired. 

Note: We are not sure if there is a limitation on how long the existing certificate has been expired.


## Multi-domain aka SAN Certificates

You can request a certificate, which will contain up to 100 domain names. These are basically server names, unless you are happy to:

1. copy your private key across servers; and
2. implement DNS load-balancing / load-distribution.

The limit here is 100 domain names per certificate. Technically it's implemented as one main "subject" and 99 alternative names.

## No wildcard certificates

Wildcard and DV (domain validation) certificates are not available. This is related to similar restrictions on EV (extended validation) certificates.

## Adding a domain name to *an existing* certificate

If you change the set of domain names - add at least one, or remove at least one, the subsequent request is counted as a new certificate. It will count towards the "Max certificate requests" limit above.

## Maximum renewals

There is a limit on the maximum number of certificate renewals. This is currently 5 per week. A request for a certificate is counted as a renewal, if it contains exactly the same set of domain names. Mind the following two friendly rules:

1. domain names are case insensitive (EnigmaBridge.com and enigmabridge.com are equal); nad
2. domains can be in any order.

## Combination of renewals and new certificates

As there are two separate rules limiting the number of requests, be aware that if you reach any of them, the request will be rejected.

Let's say that you have been testing and requested 5 certificates for "www.keychest.net" in one day (you reached the domain renewal limit) and you know these were the only request in the last 7 days$. A few hours later you realize that it would be much better to add this domain name to another certificate you already have on your server (e.g., with "mx.keychest.net", and "ssh.keychet.net"). If you now request a new certificate with all three domains (in my example here), the request will be rejected. The reason is the limit per domain renewals for "www.keychest.net". 

## Failed authorization limit

If your automation doesn't work, or you fail to validate domain ownership (e.g., via files in your web root folder, or amending your DNS records), there is a limit of 5 failed validations per domain per hour.


## Does certificate revocation reset limits?

No.

## Velocity limits 1

There is a limit of 20 per seconds for your servers (end-points) related to new certificates or revocation of certificates. If you use OCSP to verify certificates, there is a limit of 2,000/s per server.

## Velocity limits 2

When you start using LE clients, they will create an LE account for your endpoint. This client account is used to authorize your requests. Usually, you will have one account per server, but you can share the account across your servers.

If you provide hosting, you may need to create an account per each user, and all that on a dedicated server (a server with one IP address). There is a limit of 500 new accounts per IP address.

If you make a mistake somewhere, typically when changing clients, and you don't complete certificate issuance, it is possible that LE servers will keep "pending authorizations". There is a limit of 300 pending authorizations per account. If you need to clear them quicker, please follow [instructions here if you get into this situation](https://community.letsencrypt.org/t/clear-pending-authorizations/22157/2}.

Sliding window of 7 days applies here as well.

*These limits are again applied only in the Production environment!*

## Validity of authorizations

Here it gets very technical, but also interesting. There is a separate validity time limit for authorizations, which is currently 60 days. What it means is that if you request a certificate renewal, your client will not have to request new authorization for 60 days. It means that you need to:

1. spin a temporary web server; or
2. allow creation of temporary verification files in your web root folder; or
3. update DNS records.

Only once every 60 days. This authorization is per account and per domain name. If you have "ssh.enigmabrige.com" and "mx.enigmabridge.com" pointing to the samer server, but each name has its own certificate, you will need 2 authorizations.

For an authorization to live for 60 days, you have to successfully verify it within 7 days. Otherwise it may stay in the "pending" state and will be removed after 7 days. 

## Supported algorithms

[Let's Encrypt Integration Guide](https://letsencrypt.org/docs/integration-guide/) states that you can request certificates for:

1. RSA keys, with lengths from 2048b to 4096b;
2. P-256 ECDSA keys; and
3. P-384 ECDSA keys.

## Email reminders

Letsencrypt will send you reminders to renew your certificates. These are sent:

1. 20 days before the date of expiry;
2. 10 days before the date of expiry; and
3. 1 day before the expiry.

The actual time varies and can be a day or even two days later. 

These email reminders are sent to the email address you enter when you create your account key - this is usually the first certification request on a given server. The email is stored with your letsencrypt account configuration. You can also set an arbitrary email address with each request, if your Let's Encrypt client supports it.

Bear in mind that if you unsubscribe from these notifications, you can't re-subscribe. It may also happen that these emails end up in your spam / junk mail box.

Try our [free KeyChest](https://keychest.net) service to get weekly emails with all expiration dates in one place.



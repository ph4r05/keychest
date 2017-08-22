
Temporary notes for actions by hand:

disable emails:
 UPDATE users SET weekly_emails_disabled=1 WHRE id=xxxx;

adding a new email news:
 Email news will be also sent to users who register anytime during the news item validity.

 Table: email_news
Create Table: CREATE TABLE `email_news` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `schedule_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `disabled_at` datetime DEFAULT NULL,
  `message` text,
  `valid_to` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8

deleted_at, disabled_at - set to some particular date to disable/delete - leave null to keep active
schedule_at - date of activation of the news. if null, will be sent in the nearest weekly update, otherwise will be sent in nearest weekly update AFTER schedule_at in the UTC
valid_to - null for unlimited validity (still the message is sent only once) - otherwise will not be sent AFTER this date
set created_at and udated_at to NOW() when inserting the record - created_at is used as the date displayed in the email with the news

if schedule_at is set, it overrides created_at and will be used for scheduling and display

e.g.

INSERT into email_news (`created_at`,`valid_to`,`message`) values (NOW(), NOW() + INTERVAL 2 WEEK, "message");




import MySQLdb
import os
import datetime

db_password = "password"
db_user = "user"

source = "/var/www/keychest-dev/resources/assets/images/keychest_background.png"
destination = "/var/www/keychest-dev/public/images/keychest_background.png"


db = MySQLdb.connect(host="localhost", port=3306, user=db_user, passwd=db_password, db="keychest")
cursor = db.cursor()
cursor.execute("SELECT count(*) from users")
no_users = cursor.fetchone()[0]
cursor.execute("Select count(*) from watch_target")
no_targets = cursor.fetchone()[0]
cursor.execute("SELECT count(*) from api_keys")
no_api_keys = cursor.fetchone()[0]


cursor.execute("SELECT count(*) from scan_history WHERE created_at > DATE_SUB(CURDATE(), INTERVAL 1 DAY)")
cert_checks = cursor.fetchone()[0]

timestamp = datetime.datetime.now().strftime("%I:%M%p on %B %d, %Y")

os.system("/usr/bin/convert -pointsize 14 -fill white -draw 'text 900,780 \"time: %s\"' -draw 'text 900,800 \"users: %d\"' -draw 'text 900,820 \"domain names: %d\"' -draw 'text 900,840 \"TLS checks (24h): %d\"' -draw 'text 900,860 \"API keys: %s\"'  %s %s"%(timestamp, no_users, no_targets, cert_checks, no_api_keys, source, destination))

import MySQLdb
import os
import datetime

db_password = "password"
db_user = "user"

source = "/var/www/keychest-dev/resources/assets/images/login_background.png"
destination = "/var/www/keychest-dev/public/images/login_background.png"


db = MySQLdb.connect(host="localhost", port=3306, user=db_user, passwd=db_password, db="keychest")
cursor = db.cursor()
cursor.execute("SELECT count(*) from users")
no_users = cursor.fetchone()[0]
cursor.execute("Select count(*) from watch_target")
no_targets = cursor.fetchone()[0]

cursor.execute("SELECT count(*) from scan_history WHERE created_at > DATE_SUB(CURDATE(), INTERVAL 1 DAY)")
cert_checks = cursor.fetchone()[0]

timestamp = datetime.datetime.now().strftime("%I:%M%p on %B %d, %Y")

os.system("/usr/bin/convert -pointsize 20 -fill white -draw 'text 800,770 \"time: %s\"' -draw 'text 800,800 \"users: %d\"' -draw 'text 800,830 \"domain names: %d\"' -draw 'text 800,860 \"TLS checks (24h): %d\"' %s %s"%(timestamp, no_users, no_targets, cert_checks, source, destination))

